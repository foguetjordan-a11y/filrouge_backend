<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Filiere;
use App\Models\Enrollement;
use App\Models\Note;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard Admin
     */
    public function admin()
    {
        $totalEtudiants = User::role('etudiant')->count();
        $etudiantsActifs = User::role('etudiant')->where('status', 'approved')->count();
        $etudiantsEnAttente = User::role('etudiant')->where('status', 'pending')->count();

        $totalFilieres = Filiere::count();
        $totalEnrollements = Enrollement::count();

        // Taux de réussite
        $admis = Enrollement::whereHas('notes', function ($q) {
            $q->havingRaw('AVG(note) >= 10');
        })->count();

        return response()->json([
            'success' => true,
            'data' => [
                'etudiants' => [
                    'total' => $totalEtudiants,
                    'actifs' => $etudiantsActifs,
                    'en_attente' => $etudiantsEnAttente,
                ],
                'filieres' => $totalFilieres,
                'enrollements' => $totalEnrollements,
                'admis' => $admis,
            ]
        ]);
    }
    /**
     * Dashboard Étudiant
     */
    public function etudiant(Request $request)
    {
        $user = $request->user();

        $enrollement = Enrollement::where('user_id', $user->id)
            ->with(['filiere', 'niveau', 'notes.matiere'])
            ->first();

        if (!$enrollement) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun enrôlement trouvé'
            ], 404);
        }

        $moyenne = $enrollement->notes->avg('note');
        $decision = $moyenne >= 10 ? 'ADMIS' : 'AJOURNE';

        return response()->json([
            'success' => true,
            'data' => [
                'filiere' => $enrollement->filiere->nom,
                'niveau' => $enrollement->niveau->libelle,
                'moyenne' => round($moyenne, 2),
                'decision' => $decision,
                'notes' => $enrollement->notes
            ]
        ]);
    }
}
