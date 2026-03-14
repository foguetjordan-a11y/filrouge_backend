<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    /**
     * Liste des départements
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Departement::all()
        ]);
    }

    /**
     * Création d’un département (admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:departements,nom',
            'description' => 'nullable|string',
            'chef_departement' => 'nullable|string|max:255',
        ]);

        try {
            $department = Departement::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Département créé avec succès',
                'data' => $department
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du département',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un département
     */
    public function show($id)
    {
        $department = Departement::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $department
        ]);
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, $id)
    {
        $department = Departement::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255|unique:departements,nom,' . $id,
            'description' => 'nullable|string',
            'chef_departement' => 'nullable|string|max:255',
        ]);

        try {
            $department->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Département mis à jour avec succès',
                'data' => $department
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du département',
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
            $department = Departement::findOrFail($id);
            
            // Vérifier s'il y a des filières liées
            if ($department->filieres()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce département car il a des filières associées',
                ], 400);
            }

            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'Département supprimé avec succès'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du département',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
