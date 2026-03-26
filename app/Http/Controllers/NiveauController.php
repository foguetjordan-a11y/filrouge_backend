<?php

namespace App\Http\Controllers;

use App\Models\Niveau;
use Illuminate\Http\Request;

class NiveauController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Niveau::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'               => 'required|string|max:100',
            'libelle'           => 'nullable|string|max:100',
            'code'              => 'nullable|string|max:20',
            'filiere_id'        => 'nullable|integer|exists:filieres,id',
            'frais_inscription' => 'nullable|numeric|min:0',
            'description'       => 'nullable|string',
            'ordre'             => 'nullable|integer|min:1',
        ]);

        if (empty($validated['libelle'])) {
            $validated['libelle'] = $validated['nom'];
        }

        $niveau = Niveau::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Niveau cree avec succes',
            'data'    => $niveau
        ], 201);
    }

    public function show($id)
    {
        $niveau = Niveau::findOrFail($id);
        return response()->json(['success' => true, 'data' => $niveau]);
    }

    public function update(Request $request, $id)
    {
        $niveau = Niveau::findOrFail($id);

        $validated = $request->validate([
            'nom'               => 'required|string|max:100',
            'libelle'           => 'nullable|string|max:100',
            'code'              => 'nullable|string|max:20',
            'filiere_id'        => 'nullable|integer|exists:filieres,id',
            'frais_inscription' => 'nullable|numeric|min:0',
            'description'       => 'nullable|string',
            'ordre'             => 'nullable|integer|min:1',
        ]);

        if (empty($validated['libelle'])) {
            $validated['libelle'] = $validated['nom'];
        }

        $niveau->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Niveau mis a jour avec succes',
            'data'    => $niveau
        ]);
    }

    public function destroy($id)
    {
        try {
            $niveau = Niveau::findOrFail($id);

            if ($niveau->enrollements()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce niveau car il a des inscriptions associees',
                ], 400);
            }

            $niveau->delete();

            return response()->json([
                'success' => true,
                'message' => 'Niveau supprime avec succes'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du niveau',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
