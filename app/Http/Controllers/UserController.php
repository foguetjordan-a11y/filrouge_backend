<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\CompteApprouveNotification;

class UserController extends Controller
{
    /**
     * Étudiant crée son compte librement
     */
    public function create(Request $request)
    {
        // 1️⃣ Validation
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        try {
            // 2️⃣ Créer l'utilisateur
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => 'etudiant',
                'status'   => 'pending',
            ]);

            // 3️⃣ Réponse JSON
            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès. En attente d\'approbation par l\'admin.',
                'data' => [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'role'   => $user->role,
                    'status' => $user->status,
                ],
            ], 201);

        } catch (\Throwable $e) {
            // 4️⃣ Gestion des erreurs
            Log::error('Erreur création compte', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du compte: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin approuve un compte étudiant
     */
    public function approve($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->role !== 'etudiant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les comptes étudiants peuvent être approuvés.'
                ], 403);
            }

            $user->status = 'approved';
            $user->save();
            $user->notify(new CompteApprouveNotification());

            return response()->json([
                'success' => true,
                'message' => 'Compte approuvé avec succès',
                'data' => [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'status' => $user->status
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur approbation compte', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'approbation du compte: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin met à jour le statut d'un utilisateur
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,approved,rejected'
            ]);

            $user = User::findOrFail($id);

            if ($user->role !== 'etudiant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les comptes étudiants peuvent être modifiés.'
                ], 403);
            }

            $oldStatus = $user->status;
            $user->status = $validated['status'];
            $user->save();

            // Envoyer notification si approuvé
            if ($validated['status'] === 'approved' && $oldStatus !== 'approved') {
                $user->notify(new CompteApprouveNotification());
            }

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'data' => [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'status' => $user->status
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur mise à jour statut', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin supprime un étudiant
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Vérifier que c'est bien un étudiant
            if ($user->role !== 'etudiant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les comptes étudiants peuvent être supprimés.'
                ], 403);
            }

            // Vérifier s'il y a des enrôlements associés
            $enrollementsCount = $user->enrollements()->count();
            
            if ($enrollementsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cet étudiant car il a des enrôlements associés. Veuillez d\'abord traiter ses enrôlements.'
                ], 422);
            }

            // Sauvegarder les informations pour le log
            $userInfo = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ];

            // Supprimer l'utilisateur
            $user->delete();

            // Log de l'action
            Log::info('Étudiant supprimé par admin', [
                'admin_id' => auth()->id(),
                'deleted_user' => $userInfo
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Étudiant supprimé avec succès',
                'data' => $userInfo
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Étudiant non trouvé'
            ], 404);
        } catch (\Throwable $e) {
            Log::error('Erreur suppression étudiant', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'admin_id' => auth()->id(),
                'student_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'étudiant: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Liste tous les utilisateurs (admin)
     */
    public function index()
    {
        $users = User::all();
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Affiche un utilisateur
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }
}
