<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Obtenir le profil de l'utilisateur connecté
     */
    public function show()
    {
        try {
            $user = Auth::user();
            $user->load(['enrollements.filiere.departement', 'enrollements.niveau']);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'profile_completion' => [
                        'is_complete' => $user->isProfileComplete(),
                        'completed_at' => $user->profile_completed_at,
                        'missing_fields' => $this->getMissingFields($user)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du profil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le profil complet
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'date_naissance' => 'required|date|before:today|after:1950-01-01',
            'lieu_naissance' => 'required|string|max:255',
            'nationalite' => 'required|string|max:100',
            'adresse' => 'required|string|max:500',
            'telephone' => 'required|string|max:20|unique:users,telephone,' . $user->id,
            'numero_cni' => 'nullable|string|max:50|unique:users,numero_cni,' . $user->id,
            'numero_passeport' => 'nullable|string|max:50|unique:users,numero_passeport,' . $user->id,
            'photo_identite' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ];

        // Validation conditionnelle : au moins un document d'identité requis
        $request->validate($rules);

        // Vérifier qu'au moins un numéro d'identité est fourni
        if (empty($request->numero_cni) && empty($request->numero_passeport)) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez fournir au moins un numéro de CNI ou de passeport'
            ], 422);
        }

        try {
            $updateData = $request->only([
                'nom', 'prenom', 'sexe', 'date_naissance', 'lieu_naissance',
                'nationalite', 'adresse', 'telephone', 'numero_cni', 
                'numero_passeport'
            ]);

            // Gérer l'upload de la photo d'identité
            if ($request->hasFile('photo_identite')) {
                // Supprimer l'ancienne photo si elle existe
                if ($user->photo_identite) {
                    Storage::disk('public')->delete($user->photo_identite);
                }

                $photoPath = $request->file('photo_identite')->store('photos_identite', 'public');
                $updateData['photo_identite'] = $photoPath;
            }

            // Mettre à jour l'utilisateur
            $user->update($updateData);

            // Vérifier si le profil est maintenant complet
            if ($user->isProfileComplete()) {
                $user->markProfileAsComplete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'data' => [
                    'user' => $user->fresh(),
                    'profile_complete' => $user->isProfileComplete(),
                    'can_enroll' => $user->isProfileComplete() && $user->status === 'approved'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour uniquement les informations de base
     */
    public function updateBasicInfo(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'telephone' => 'sometimes|required|string|max:20|unique:users,telephone,' . $user->id,
            'adresse' => 'sometimes|required|string|max:500'
        ]);

        try {
            $updateData = $request->only(['nom', 'prenom', 'telephone', 'adresse']);
            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Informations de base mises à jour',
                'data' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe actuel est incorrect'
            ], 422);
        }

        try {
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe modifié avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de mot de passe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload de la photo d'identité
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo_identite' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $user = Auth::user();

            // Supprimer l'ancienne photo si elle existe
            if ($user->photo_identite) {
                Storage::disk('public')->delete($user->photo_identite);
            }

            $photoPath = $request->file('photo_identite')->store('photos_identite', 'public');
            
            $user->update(['photo_identite' => $photoPath]);

            return response()->json([
                'success' => true,
                'message' => 'Photo d\'identité uploadée avec succès',
                'data' => [
                    'photo_path' => $photoPath,
                    'photo_url' => Storage::url($photoPath)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier la complétude du profil
     */
    public function checkCompletion()
    {
        try {
            $user = Auth::user();
            
            $completion = [
                'is_complete' => $user->isProfileComplete(),
                'completed_at' => $user->profile_completed_at,
                'missing_fields' => $this->getMissingFields($user),
                'completion_percentage' => $this->getCompletionPercentage($user)
            ];

            return response()->json([
                'success' => true,
                'data' => $completion
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les champs manquants du profil
     */
    private function getMissingFields(User $user): array
    {
        $requiredFields = [
            'nom' => 'Nom de famille',
            'prenom' => 'Prénom(s)',
            'sexe' => 'Sexe',
            'date_naissance' => 'Date de naissance',
            'lieu_naissance' => 'Lieu de naissance',
            'telephone' => 'Numéro de téléphone',
            'adresse' => 'Adresse complète'
        ];

        $missingFields = [];

        foreach ($requiredFields as $field => $label) {
            if (empty($user->$field)) {
                $missingFields[] = [
                    'field' => $field,
                    'label' => $label
                ];
            }
        }

        // Vérifier qu'au moins un document d'identité est présent
        if (empty($user->numero_cni) && empty($user->numero_passeport)) {
            $missingFields[] = [
                'field' => 'document_identite',
                'label' => 'Numéro de CNI ou Passeport'
            ];
        }

        return $missingFields;
    }

    /**
     * Calculer le pourcentage de complétude du profil
     */
    private function getCompletionPercentage(User $user): int
    {
        $totalFields = 8; // 7 champs obligatoires + 1 document d'identité
        $completedFields = 0;

        $requiredFields = ['nom', 'prenom', 'sexe', 'date_naissance', 'lieu_naissance', 'telephone', 'adresse'];

        foreach ($requiredFields as $field) {
            if (!empty($user->$field)) {
                $completedFields++;
            }
        }

        // Vérifier les documents d'identité
        if (!empty($user->numero_cni) || !empty($user->numero_passeport)) {
            $completedFields++;
        }

        return round(($completedFields / $totalFields) * 100);
    }

    /**
     * Obtenir les profils incomplets (Admin uniquement)
     */
    public function incompleteProfiles()
    {
        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $incompleteUsers = User::where('is_profile_complete', false)
                                 ->where('role', 'etudiant')
                                 ->with(['enrollements'])
                                 ->orderBy('created_at', 'desc')
                                 ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $incompleteUsers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }
}