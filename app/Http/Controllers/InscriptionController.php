<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InscriptionController extends Controller
{
    /**
     * Lister toutes les inscriptions de l'étudiant connecté
     */
    public function index()
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $inscriptions = Inscription::with(['filiere', 'academicYear'])
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $inscriptions
        ], 200);
    }

    /**
     * Créer une nouvelle inscription pour l'étudiant connecté
     */
    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $validated = $request->validate([
            'filiere_id' => 'required|exists:filieres,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        // Vérification inscription existante pour cette année
        $exists = Inscription::where('user_id', $user->id)
            ->where('academic_year_id', $validated['academic_year_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà inscrit pour cette année académique'
            ], 400);
        }

        $inscription = Inscription::create([
            'user_id' => $user->id,
            'filiere_id' => $validated['filiere_id'],
            'academic_year_id' => $validated['academic_year_id'],
            'status' => 'pending', // statut par défaut
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inscription créée avec succès. En attente de validation par l’admin.',
            'data' => $inscription
        ], 201);
    }

    /**
     * Admin approuve une inscription
     */
    public function approve($id)
    {
        $inscription = Inscription::findOrFail($id);
        $inscription->status = 'validated';
        $inscription->save();

        return response()->json([
            'success' => true,
            'message' => 'Inscription approuvée avec succès',
            'data' => $inscription
        ], 200);
    }

    /**
     * Admin rejette une inscription
     */
    public function reject($id)
    {
        $inscription = Inscription::findOrFail($id);
        $inscription->status = 'rejected';
        $inscription->save();

        return response()->json([
            'success' => true,
            'message' => 'Inscription rejetée',
            'data' => $inscription
        ], 200);
    }
}
