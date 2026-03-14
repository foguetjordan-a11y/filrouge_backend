<?php

namespace App\Http\Controllers;

use App\Models\Niveau;
use Illuminate\Http\Request;

class NiveauController extends Controller
{
    /**
     * Liste des niveaux
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Niveau::all()
        ]);
    }

    /**
     * Création d’un niveau (admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'nullable|string|max:50',
            'libelle' => 'required|string|max:100|unique:niveaux,libelle',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer|min:1'
        ]);

        $niveau = Niveau::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Niveau créé avec succès',
            'data' => $niveau
        ], 201);
    }

    /**
     * Afficher un niveau
     */
    public function show($id)
    {
        $niveau = Niveau::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $niveau
        ]);
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, $id)
    {
        $niveau = Niveau::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'nullable|string|max:50',
            'libelle' => 'required|string|max:100|unique:niveaux,libelle,' . $niveau->id,
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer|min:1'
        ]);

        try {
            $niveau->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Niveau mis à jour avec succès',
                'data' => $niveau
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du niveau',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        try {
            $niveau = Niveau::findOrFail($id);
            
            // Vérifier s'il y a des inscriptions liées
            if ($niveau->enrollements()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce niveau car il a des inscriptions associées',
                ], 400);
            }

            $niveau->delete();

            return response()->json([
                'success' => true,
                'message' => 'Niveau supprimé avec succès'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du niveau',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
