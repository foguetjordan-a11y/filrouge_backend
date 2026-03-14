<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enrollement;
use App\Models\Filiere;
use App\Models\Departement;
use App\Models\Niveau;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Rapport PDF des étudiants enrôlés
     */
    public function studentsEnrolledReport(Request $request)
    {
        try {
            Log::info('Génération rapport étudiants enrôlés');
            
            $query = User::where('role', 'etudiant')
                ->where('status', 'approved')
                ->with(['enrollements.filiere.departement', 'enrollements.niveau']);

            // Filtres optionnels
            if ($request->has('filiere_id') && $request->filiere_id) {
                $query->whereHas('enrollements', function($q) use ($request) {
                    $q->where('filiere_id', $request->filiere_id);
                });
            }

            if ($request->has('niveau_id') && $request->niveau_id) {
                $query->whereHas('enrollements', function($q) use ($request) {
                    $q->where('niveau_id', $request->niveau_id);
                });
            }

            if ($request->has('statut') && $request->statut) {
                $query->whereHas('enrollements', function($q) use ($request) {
                    $q->where('statut', $request->statut);
                });
            }

            $students = $query->get();
            
            Log::info('Étudiants récupérés: ' . $students->count());
            
            $data = [
                'title' => 'Liste des Étudiants Enrôlés',
                'students' => $students,
                'generated_at' => now()->format('d/m/Y H:i'),
                'total_count' => $students->count(),
                'filters' => [
                    'filiere' => $request->filiere_id ? Filiere::find($request->filiere_id)?->nom : 'Toutes',
                    'niveau' => $request->niveau_id ? Niveau::find($request->niveau_id)?->libelle : 'Tous',
                    'statut' => $request->statut ?: 'Tous'
                ]
            ];

            Log::info('Données préparées pour PDF');

            $pdf = Pdf::loadView('pdf.reports.students-enrolled', $data);
            $pdf->setPaper('A4', 'portrait');

            Log::info('PDF généré avec succès');

            return $pdf->download('etudiants-enrolles-' . now()->format('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur génération rapport étudiants: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rapport PDF des demandes en attente
     */
    public function pendingApplicationsReport(Request $request)
    {
        try {
            Log::info('Génération rapport demandes en attente');
            
            $pendingEnrollments = Enrollement::where('statut', 'en_attente')
                ->with(['etudiant', 'filiere.departement', 'niveau'])
                ->orderBy('date_enrollement', 'desc')
                ->get();

            Log::info('Demandes en attente récupérées: ' . $pendingEnrollments->count());

            $data = [
                'title' => 'Demandes d\'Inscription en Attente',
                'enrollments' => $pendingEnrollments,
                'generated_at' => now()->format('d/m/Y H:i'),
                'total_count' => $pendingEnrollments->count()
            ];

            $pdf = Pdf::loadView('pdf.reports.pending-applications', $data);
            $pdf->setPaper('A4', 'portrait');

            Log::info('PDF demandes en attente généré avec succès');

            return $pdf->download('demandes-en-attente-' . now()->format('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur génération rapport demandes: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rapport PDF par filière
     */
    public function filiereReport(Request $request, $id)
    {
        try {
            Log::info('Génération rapport filière: ' . $id);
            
            $filiere = Filiere::with(['departement'])->findOrFail($id);
            
            $enrollments = Enrollement::where('filiere_id', $id)
                ->with(['etudiant', 'niveau'])
                ->orderBy('date_enrollement', 'desc')
                ->get();

            $stats = [
                'total' => $enrollments->count(),
                'en_attente' => $enrollments->where('statut', 'en_attente')->count(),
                'valide' => $enrollments->where('statut', 'valide')->count(),
                'rejete' => $enrollments->where('statut', 'rejete')->count()
            ];

            $data = [
                'title' => 'Rapport Filière: ' . $filiere->nom,
                'filiere' => $filiere,
                'enrollments' => $enrollments,
                'stats' => $stats,
                'generated_at' => now()->format('d/m/Y H:i')
            ];

            $pdf = Pdf::loadView('pdf.reports.filiere-report', $data);
            $pdf->setPaper('A4', 'portrait');

            Log::info('PDF rapport filière généré avec succès');

            return $pdf->download('rapport-filiere-' . \Illuminate\Support\Str::slug($filiere->nom) . '-' . now()->format('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur génération rapport filière: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rapport PDF par département
     */
    public function departementReport(Request $request, $id)
    {
        try {
            Log::info('Génération rapport département: ' . $id);
            
            $departement = Departement::with(['filieres'])->findOrFail($id);
            
            $enrollments = Enrollement::whereHas('filiere', function($q) use ($id) {
                $q->where('departement_id', $id);
            })
            ->with(['etudiant', 'filiere', 'niveau'])
            ->orderBy('date_enrollement', 'desc')
            ->get();

            $stats = [
                'total_filieres' => $departement->filieres->count(),
                'total_enrollments' => $enrollments->count(),
                'en_attente' => $enrollments->where('statut', 'en_attente')->count(),
                'valide' => $enrollments->where('statut', 'valide')->count(),
                'rejete' => $enrollments->where('statut', 'rejete')->count()
            ];

            $data = [
                'title' => 'Rapport Département: ' . $departement->nom,
                'departement' => $departement,
                'enrollments' => $enrollments,
                'stats' => $stats,
                'generated_at' => now()->format('d/m/Y H:i')
            ];

            $pdf = Pdf::loadView('pdf.reports.departement-report', $data);
            $pdf->setPaper('A4', 'portrait');

            Log::info('PDF rapport département généré avec succès');

            return $pdf->download('rapport-departement-' . \Illuminate\Support\Str::slug($departement->nom) . '-' . now()->format('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur génération rapport département: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rapport PDF des statistiques globales
     */
    public function globalStatsReport(Request $request)
    {
        try {
            // Statistiques générales
            $totalStudents = User::where('role', 'etudiant')->count();
            $approvedStudents = User::where('role', 'etudiant')->where('status', 'approved')->count();
            $pendingStudents = User::where('role', 'etudiant')->where('status', 'pending')->count();
            
            $totalEnrollments = Enrollement::count();
            $pendingEnrollments = Enrollement::where('statut', 'en_attente')->count();
            $validatedEnrollments = Enrollement::where('statut', 'valide')->count();
            $rejectedEnrollments = Enrollement::where('statut', 'rejete')->count();

            // Statistiques par filière
            $filiereStats = Filiere::withCount(['enrollements'])
                ->with(['departement'])
                ->get()
                ->map(function($filiere) {
                    return [
                        'nom' => $filiere->nom,
                        'departement' => $filiere->departement->nom ?? 'N/A',
                        'total_enrollments' => $filiere->enrollements_count,
                        'en_attente' => $filiere->enrollements()->where('statut', 'en_attente')->count(),
                        'valide' => $filiere->enrollements()->where('statut', 'valide')->count(),
                        'rejete' => $filiere->enrollements()->where('statut', 'rejete')->count()
                    ];
                });

            // Évolution mensuelle (6 derniers mois)
            $monthlyStats = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthlyStats[] = [
                    'month' => $date->format('M Y'),
                    'enrollments' => Enrollement::whereYear('date_enrollement', $date->year)
                        ->whereMonth('date_enrollement', $date->month)
                        ->count()
                ];
            }

            $data = [
                'title' => 'Statistiques Globales du Système',
                'generated_at' => now()->format('d/m/Y H:i'),
                'period' => 'Année académique ' . (now()->year - 1) . '-' . now()->year,
                'general_stats' => [
                    'total_students' => $totalStudents,
                    'approved_students' => $approvedStudents,
                    'pending_students' => $pendingStudents,
                    'total_enrollments' => $totalEnrollments,
                    'pending_enrollments' => $pendingEnrollments,
                    'validated_enrollments' => $validatedEnrollments,
                    'rejected_enrollments' => $rejectedEnrollments
                ],
                'filiere_stats' => $filiereStats,
                'monthly_stats' => $monthlyStats
            ];

            $pdf = Pdf::loadView('pdf.reports.global-stats', $data);
            $pdf->setPaper('A4', 'portrait');

            return $pdf->download('statistiques-globales-' . now()->format('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur génération rapport global: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rapport PDF par niveau
     */
    public function niveauReport(Request $request, $id)
    {
        try {
            Log::info('Génération rapport niveau: ' . $id);
            
            $niveau = Niveau::findOrFail($id);
            
            $enrollments = Enrollement::where('niveau_id', $id)
                ->with(['etudiant', 'filiere.departement'])
                ->orderBy('date_enrollement', 'desc')
                ->get();

            $stats = [
                'total' => $enrollments->count(),
                'en_attente' => $enrollments->where('statut', 'en_attente')->count(),
                'valide' => $enrollments->where('statut', 'valide')->count(),
                'rejete' => $enrollments->where('statut', 'rejete')->count()
            ];

            // Répartition par filière
            $filiereBreakdown = $enrollments->groupBy('filiere.nom')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'en_attente' => $group->where('statut', 'en_attente')->count(),
                    'valide' => $group->where('statut', 'valide')->count(),
                    'rejete' => $group->where('statut', 'rejete')->count()
                ];
            });

            $data = [
                'title' => 'Rapport Niveau: ' . $niveau->libelle,
                'niveau' => $niveau,
                'enrollments' => $enrollments,
                'stats' => $stats,
                'filiere_breakdown' => $filiereBreakdown,
                'generated_at' => now()->format('d/m/Y H:i')
            ];

            $pdf = Pdf::loadView('pdf.reports.niveau-report', $data);
            $pdf->setPaper('A4', 'portrait');

            Log::info('PDF rapport niveau généré avec succès');

            return $pdf->download('rapport-niveau-' . \Illuminate\Support\Str::slug($niveau->libelle) . '-' . now()->format('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur génération rapport niveau: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport: ' . $e->getMessage()
            ], 500);
        }
    }
}