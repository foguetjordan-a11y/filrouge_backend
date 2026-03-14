<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    /**
     * Lister toutes les années académiques
     */
    public function index()
    {
        $years = AcademicYear::all();
        return response()->json([
            'success' => true,
            'data' => $years
        ], 200);
    }

    /**
     * Créer une nouvelle année académique
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:academic_years,name'
        ]);

        $year = AcademicYear::create([
            'name' => $validated['name'],
            'is_active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Année académique créée',
            'data' => $year
        ], 201);
    }

    /**
     * Activer une année académique
     */
    public function setActive($id)
    {
        // Désactiver toutes les autres années
        AcademicYear::query()->update(['is_active' => false]);

        // Activer celle choisie
        $year = AcademicYear::findOrFail($id);
        $year->is_active = true;
        $year->save();

        return response()->json([
            'success' => true,
            'message' => "Année académique {$year->name} activée",
            'data' => $year
        ], 200);
    }
}
