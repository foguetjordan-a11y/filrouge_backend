<?php

namespace App\Http\Controllers;
use App\Models\Enrollement;
use App\Models\Quitus;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use App\Notifications\EnrollementValideNotification;
use App\Notifications\EnrollementSoumisNotification;
use App\Notifications\EnrollementRejeteNotification;
use App\Notifications\NouvelEnrollementAdminNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollementController extends Controller
{
    /**
     * Liste tous les enrollements (admin)
     */
    public function index()
    {
        try {
            $enrollements = Enrollement::with(['etudiant', 'filiere', 'niveau', 'anneeAcademique'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $enrollements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des enrollements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste les enrollements de l'étudiant connecté
     */
    public function mesEnrollements(Request $request)
    {
        try {
            $enrollements = Enrollement::with(['filiere', 'niveau', 'anneeAcademique'])
                ->where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $enrollements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de vos enrollements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Étudiant s'enrôle avec formulaire professionnel simplifié
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Informations académiques essentielles
            'filiere_id' => 'required|exists:filieres,id',
            'niveau_id'  => 'required|exists:niveaux,id',
            'annee_academique_id' => 'required|exists:academic_years,id',
            'type_inscription' => 'required|in:nouvelle,reinscription',
            
            // Notes optionnelles
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $user = $request->user();

            // Vérifier que le profil utilisateur est complet
            if (!$user->isProfileComplete()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre profil doit être complété avant de pouvoir vous enrôler. Veuillez compléter vos informations personnelles dans votre profil.',
                    'required_fields' => [
                        'nom', 'prenom', 'sexe', 'date_naissance', 
                        'lieu_naissance', 'telephone', 'adresse'
                    ]
                ], 400);
            }

            // Vérifier si l'étudiant a un enrôlement actif pour cette année académique
            $existingEnrollment = Enrollement::where('user_id', $user->id)
                ->where('annee_academique_id', $validated['annee_academique_id'])
                ->whereIn('statut', ['en_attente', 'valide', 'paye'])
                ->first();

            if ($existingEnrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous avez déjà un enrôlement actif pour cette année académique. Statut actuel: ' . $existingEnrollment->statut,
                    'existing_enrollment' => [
                        'id' => $existingEnrollment->id,
                        'statut' => $existingEnrollment->statut,
                        'filiere' => $existingEnrollment->filiere->nom ?? 'N/A',
                        'date_enrollement' => $existingEnrollment->date_enrollement->format('d/m/Y')
                    ]
                ], 409);
            }

            // Si un enrôlement rejeté existe, le supprimer pour permettre une nouvelle inscription
            $rejectedEnrollment = Enrollement::where('user_id', $user->id)
                ->where('annee_academique_id', $validated['annee_academique_id'])
                ->where('statut', 'rejete')
                ->first();

            if ($rejectedEnrollment) {
                $rejectedEnrollment->delete();
                Log::info("Ancien enrôlement rejeté supprimé pour permettre une nouvelle inscription", [
                    'user_id' => $user->id,
                    'old_enrollment_id' => $rejectedEnrollment->id
                ]);
            }

            // Vérifier que l'année académique est active
            $academicYear = \App\Models\AcademicYear::find($validated['annee_academique_id']);
            if (!$academicYear || !$academicYear->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'année académique sélectionnée n\'est pas active.'
                ], 400);
            }

            // Créer l'enrôlement avec les informations essentielles
            $enrollement = Enrollement::create([
                'user_id' => $user->id,
                'filiere_id' => $validated['filiere_id'],
                'niveau_id' => $validated['niveau_id'],
                'annee_academique_id' => $validated['annee_academique_id'],
                'type_inscription' => $validated['type_inscription'],
                'date_enrollement' => now(),
                'statut' => 'en_attente',
                'payment_status' => 'not_required'
            ]);

            // Charger les relations pour la réponse
            $enrollement->load(['filiere.departement', 'niveau', 'anneeAcademique', 'user']);

            // Notifier l'étudiant de la soumission
            $user->notify(new EnrollementSoumisNotification($enrollement));

            // Notifier tous les admins du nouvel enrôlement
            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new NouvelEnrollementAdminNotification($enrollement));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enrôlement effectué avec succès ! Votre dossier est en cours de traitement par l\'administration.',
                'data' => [
                    'enrollement' => $enrollement,
                    'student_profile' => [
                        'nom_complet' => $user->full_name,
                        'email' => $user->email,
                        'telephone' => $user->telephone,
                        'profil_complet' => $user->is_profile_complete
                    ],
                    'workflow_steps' => [
                        '✅ 1. Enrôlement soumis',
                        '⏳ 2. Validation par l\'administration',
                        '⏳ 3. Génération automatique de la facture',
                        '⏳ 4. Paiement des frais d\'enrôlement',
                        '⏳ 5. Génération automatique du matricule',
                        '⏳ 6. Téléchargement du quitus'
                    ]
                ]
            ], 201);
            
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà enrôlé pour cette année académique'
            ], 409);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'enrôlement: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'data' => $validated
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enrôlement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin valide un enrôlement
     */
    public function validateEnrollement($id)
    {
        try {
            DB::beginTransaction();

            $enrollement = Enrollement::with(['etudiant', 'filiere.departement', 'niveau', 'anneeAcademique'])
                ->findOrFail($id);
            
            // Vérifier que l'enrôlement peut être validé
            if ($enrollement->statut !== 'en_attente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet enrôlement ne peut pas être validé (statut actuel: ' . $enrollement->statut . ')'
                ], 400);
            }

            // Mettre à jour le statut de l'enrôlement
            $enrollement->update(['statut' => 'valide']);
            
            // Générer automatiquement la facture et configurer le paiement
            $invoiceService = app(InvoiceService::class);
            $invoice = $invoiceService->generateForEnrollment($enrollement);
            
            // Marquer l'enrôlement comme nécessitant un paiement
            $enrollement->markAsPaymentRequired(
                $invoice->total_amount,
                $invoice->due_date
            );

            // Notifier l'étudiant de la validation ET de la facture générée
            if ($enrollement->etudiant) {
                $enrollement->etudiant->notify(new EnrollementValideNotification($enrollement));
            }

            DB::commit();

            Log::info("Enrôlement validé avec génération de facture", [
                'enrollement_id' => $enrollement->id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total_amount
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Enrôlement validé avec succès. Une facture a été générée et l\'étudiant a été notifié.',
                'data' => [
                    'enrollement' => $enrollement->fresh()->load(['payments', 'invoices']),
                    'invoice' => $invoice,
                    'next_step' => 'L\'étudiant doit maintenant procéder au paiement'
                ]
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation de l\'enrôlement: ' . $e->getMessage(), [
                'enrollement_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin rejette un enrôlement
     */
    public function rejectEnrollement(Request $request, $id)
    {
        $validated = $request->validate([
            'motif_rejet' => 'nullable|string|max:500'
        ]);

        try {
            $enrollement = Enrollement::with(['etudiant', 'filiere.departement', 'niveau', 'anneeAcademique'])
                ->findOrFail($id);
            
            $enrollement->update([
                'statut' => 'rejete',
                'motif_rejet' => $validated['motif_rejet'] ?? null
            ]);
            
            // Notifier l'étudiant du rejet
            if ($enrollement->etudiant) {
                $enrollement->etudiant->notify(new EnrollementRejeteNotification($enrollement, $validated['motif_rejet'] ?? null));
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Enrôlement rejeté avec notification envoyée à l\'étudiant',
                'data' => $enrollement->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadQuitus(Request $request, $id)
    {
        try {
            $request->validate([
                'quitus' => 'required|file|mimes:pdf|max:2048',
                'reference' => 'required|string',
                'date_emission' => 'required|date',
            ]);

            $enrollement = Enrollement::findOrFail($id);
            
            $path = $request->file('quitus')->store('quitus', 'public');

            $quitus = Quitus::create([
                'user_id' => $enrollement->user_id,
                'reference' => $request->reference,
                'date_emission' => $request->date_emission,
                'statut' => 'valide',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quitus téléversé avec succès',
                'data' => [
                    'quitus' => $quitus,
                    'file_path' => $path
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadQuitus($id)
    {
        try {
            $enrollement = Enrollement::findOrFail($id);
            $quitus = Quitus::where('user_id', $enrollement->user_id)->first();

            if (!$quitus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun quitus trouvé pour cet utilisateur'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $quitus
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement: ' . $e->getMessage()
            ], 500);
        }
    }
}