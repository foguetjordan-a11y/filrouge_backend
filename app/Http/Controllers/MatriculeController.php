<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enrollement;
use App\Services\MatriculeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MatriculeController extends Controller
{
    protected $matriculeService;

    public function __construct(MatriculeService $matriculeService)
    {
        $this->matriculeService = $matriculeService;
    }

    /**
     * Générer un matricule pour un étudiant (Admin uniquement)
     */
    public function generate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'enrollement_id' => 'nullable|exists:enrollements,id'
        ]);

        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les administrateurs peuvent générer des matricules'
                ], 403);
            }

            $etudiant = User::findOrFail($request->user_id);
            $enrollement = null;

            if ($request->enrollement_id) {
                $enrollement = Enrollement::findOrFail($request->enrollement_id);
                
                // Vérifier que l'enrôlement appartient à l'étudiant
                if ($enrollement->user_id !== $etudiant->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'L\'enrôlement ne correspond pas à l\'étudiant'
                    ], 400);
                }
            }

            $matricule = $this->matriculeService->generateMatricule($etudiant, $enrollement);

            return response()->json([
                'success' => true,
                'message' => 'Matricule généré avec succès',
                'data' => [
                    'matricule' => $matricule,
                    'etudiant' => $etudiant->fresh(['enrollements.filiere', 'enrollements.niveau']),
                    'generated_at' => now()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du matricule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Vérifier si un étudiant peut avoir un matricule
     */
    public function checkEligibility($userId)
    {
        try {
            $user = Auth::user();
            $etudiant = User::findOrFail($userId);

            // Vérifier les permissions
            if ($user->role !== 'admin' && $user->id !== $etudiant->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $eligibility = [
                'can_generate' => $etudiant->canGenerateMatricule(),
                'has_matricule' => $etudiant->hasMatricule(),
                'is_profile_complete' => $etudiant->isProfileComplete(),
                'is_approved' => $etudiant->status === 'approved',
                'is_student' => $etudiant->role === 'etudiant',
                'current_matricule' => $etudiant->matricule,
                'generated_at' => $etudiant->matricule_generated_at
            ];

            // Vérifier les enrôlements validés
            $validEnrollements = $etudiant->enrollements()
                                        ->where('statut', 'valide')
                                        ->with(['filiere.departement', 'niveau'])
                                        ->get();

            $eligibility['valid_enrollements'] = $validEnrollements;
            $eligibility['has_valid_enrollement'] = $validEnrollements->count() > 0;

            // Vérifier les paiements
            $paidEnrollements = $validEnrollements->filter(function ($enrollement) {
                return $enrollement->isPaid();
            });

            $eligibility['paid_enrollements'] = $paidEnrollements->values();
            $eligibility['has_paid_enrollement'] = $paidEnrollements->count() > 0;

            return response()->json([
                'success' => true,
                'data' => $eligibility
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechercher un étudiant par matricule
     */
    public function search(Request $request)
    {
        $request->validate([
            'matricule' => 'required|string|min:3'
        ]);

        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les administrateurs peuvent rechercher par matricule'
                ], 403);
            }

            $matricule = $request->matricule;
            
            $etudiants = User::etudiants()
                           ->where('matricule', 'LIKE', "%{$matricule}%")
                           ->with(['enrollements.filiere', 'enrollements.niveau'])
                           ->limit(10)
                           ->get();

            return response()->json([
                'success' => true,
                'data' => $etudiants,
                'count' => $etudiants->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des matricules (Admin uniquement)
     */
    public function statistics()
    {
        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $stats = $this->matriculeService->getMatriculeStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les étudiants sans matricule (Admin uniquement)
     */
    public function withoutMatricule()
    {
        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $etudiants = User::etudiants()
                           ->withoutMatricule()
                           ->where('status', 'approved')
                           ->with(['enrollements' => function ($query) {
                               $query->where('statut', 'valide')
                                     ->with(['filiere.departement', 'niveau']);
                           }])
                           ->orderBy('created_at', 'desc')
                           ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $etudiants
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider le format d'un matricule
     */
    public function validateFormat(Request $request)
    {
        $request->validate([
            'matricule' => 'required|string'
        ]);

        try {
            $isValid = $this->matriculeService->validateMatriculeFormat($request->matricule);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'matricule' => $request->matricule,
                    'is_valid' => $isValid,
                    'format_expected' => 'IUC-YYYY-DEP-NIV-NNNN (ex: IUC-2025-INF-L1-0001)'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le matricule de l'étudiant connecté
     */
    public function myMatricule()
    {
        try {
            $user = Auth::user();
            
            if ($user->role !== 'etudiant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette fonction est réservée aux étudiants'
                ], 403);
            }

            $data = [
                'matricule' => $user->matricule,
                'has_matricule' => $user->hasMatricule(),
                'generated_at' => $user->matricule_generated_at,
                'can_generate' => $user->canGenerateMatricule(),
                'profile_complete' => $user->isProfileComplete()
            ];

            // Si pas de matricule, donner des informations sur les conditions
            if (!$user->hasMatricule()) {
                $data['requirements'] = [
                    'profile_complete' => $user->isProfileComplete(),
                    'account_approved' => $user->status === 'approved',
                    'valid_enrollement' => $user->enrollements()->where('statut', 'valide')->exists(),
                    'paid_enrollement' => $user->enrollements()->where('payment_status', 'paid')->exists()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }
}