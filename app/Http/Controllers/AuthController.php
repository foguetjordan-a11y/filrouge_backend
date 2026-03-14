<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * LOGIN pour admin et étudiant
     * POST /api/login
     */
 public function login(Request $request)
    {
        // Validation
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects'
            ], 401);
        }

        // Vérification statut pour étudiants
        if ($user->role === 'etudiant' && $user->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est en attente d\'approbation par l\'admin'
            ], 403);
        }

        // Supprimer anciens tokens et créer un nouveau
        $user->tokens()->delete();
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status ?? null,
                ],
            ],
        ]);
    }
    /**
     * PROFIL UTILISATEUR CONNECTÉ
     * GET /api/me
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                // Ajouter tous les champs du profil complet
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'sexe' => $user->sexe,
                'date_naissance' => $user->date_naissance,
                'lieu_naissance' => $user->lieu_naissance,
                'nationalite' => $user->nationalite,
                'adresse' => $user->adresse,
                'telephone' => $user->telephone,
                'photo_identite' => $user->photo_identite,
                'numero_cni' => $user->numero_cni,
                'numero_passeport' => $user->numero_passeport,
                'matricule' => $user->matricule,
                'type_inscription' => $user->type_inscription,
                'matricule_generated_at' => $user->matricule_generated_at,
                'profile_completed_at' => $user->profile_completed_at,
                'is_profile_complete' => $user->is_profile_complete,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * LOGOUT
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie',
        ]);
    }

}
