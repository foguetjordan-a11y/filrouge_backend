<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FiliereController extends Controller
{
    /**
     * Créer une filière
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|unique:filieres,nom',
            'departement_id' => 'required|integer|exists:departements,id',
        ]);

        try {
            $filiere = Filiere::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Filière créée',
                'data' => $filiere
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Erreur création filière', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la filière',
            ], 500);
        }
    }

    /**
     * Lister toutes les filières
     */
    public function index()
    {
        $filieres = Filiere::with('departement')->get();

        return response()->json([
            'success' => true,
            'data' => $filieres
        ], 200);
    }

    /**
     * Mettre à jour une filière
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nom' => 'required|string|unique:filieres,nom,' . $id,
            'departement_id' => 'required|integer|exists:departements,id',
            'description' => 'nullable|string',
            'duree_etudes' => 'nullable|string',
            'diplome_delivre' => 'nullable|string',
        ]);

        try {
            $filiere = Filiere::findOrFail($id);
            $filiere->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Filière mise à jour',
                'data' => $filiere->load('departement')
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Erreur mise à jour filière', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la filière',
            ], 500);
        }
    }

    /**
     * Supprimer une filière
     */
    public function destroy($id)
    {
        try {
            $filiere = Filiere::findOrFail($id);
            
            // Vérifier s'il y a des inscriptions liées
            if ($filiere->enrollements()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cette filière car elle a des inscriptions associées',
                ], 400);
            }

            $filiere->delete();

            return response()->json([
                'success' => true,
                'message' => 'Filière supprimée avec succès',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Erreur suppression filière', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la filière',
            ], 500);
        }
    }
}
