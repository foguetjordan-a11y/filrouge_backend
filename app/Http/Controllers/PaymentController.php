<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Enrollement;
use App\Services\PaymentService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $invoiceService;

    public function __construct(PaymentService $paymentService, InvoiceService $invoiceService)
    {
        $this->paymentService = $paymentService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Liste des paiements (Admin: tous, Étudiant: ses paiements)
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Payment::with(['user', 'enrollement.filiere', 'invoice', 'paymentMethod']);

            // Si étudiant, filtrer ses paiements uniquement
            if ($user->role === 'etudiant') {
                $query->where('user_id', $user->id);
            }

            // Filtres pour admin
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('payment_method')) {
                $query->where('payment_method_id', $request->payment_method);
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $payments = $query->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $payments
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des paiements: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paiements'
            ], 500);
        }
    }

    /**
     * Détails d'un paiement
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $query = Payment::with(['user', 'enrollement.filiere', 'invoice', 'paymentMethod']);

            // Si étudiant, vérifier que c'est son paiement
            if ($user->role === 'etudiant') {
                $query->where('user_id', $user->id);
            }

            $payment = $query->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement non trouvé'
            ], 404);
        }
    }

    /**
     * Initier un paiement pour un enrôlement
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'enrollement_id' => 'required|exists:enrollements,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $enrollement = Enrollement::findOrFail($request->enrollement_id);

            // Vérifier que l'enrôlement appartient à l'utilisateur
            if ($user->role === 'etudiant' && $enrollement->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à cet enrôlement'
                ], 403);
            }

            // Vérifier que l'enrôlement est validé
            if ($enrollement->status !== 'valide') {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'enrôlement doit être validé avant le paiement'
                ], 400);
            }

            // Vérifier s'il n'y a pas déjà un paiement réussi
            $existingPayment = Payment::where('enrollement_id', $enrollement->id)
                                    ->where('status', 'completed')
                                    ->first();

            if ($existingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet enrôlement a déjà été payé'
                ], 400);
            }

            // Créer ou récupérer la facture
            $invoice = $this->invoiceService->getOrCreateForEnrollment($enrollement);

            // Initier le paiement
            $payment = $this->paymentService->initiatePayment([
                'user_id' => $enrollement->user_id,
                'enrollement_id' => $enrollement->id,
                'invoice_id' => $invoice->id,
                'payment_method_id' => $request->payment_method_id,
                'amount' => $request->amount
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement initié avec succès',
                'data' => [
                    'payment' => $payment->load(['paymentMethod', 'invoice']),
                    'payment_instructions' => $this->paymentService->getPaymentInstructions($payment)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'initiation du paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initiation du paiement'
            ], 500);
        }
    }

    /**
     * Confirmer un paiement (simulation ou callback)
     */
    public function confirm(Request $request, $id)
    {
        $request->validate([
            'transaction_id' => 'nullable|string',
            'external_reference' => 'nullable|string',
            'payment_details' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($id);

            // Vérifier les permissions
            $user = Auth::user();
            if ($user->role === 'etudiant' && $payment->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            // Confirmer le paiement
            $result = $this->paymentService->confirmPayment($payment, [
                'transaction_id' => $request->transaction_id,
                'external_reference' => $request->external_reference,
                'payment_details' => $request->payment_details ?? []
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement confirmé avec succès',
                'data' => $payment->fresh()->load(['paymentMethod', 'invoice', 'enrollement'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la confirmation du paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation du paiement'
            ], 500);
        }
    }

    /**
     * Annuler un paiement
     */
    public function cancel($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            $user = Auth::user();

            // Vérifier les permissions
            if ($user->role === 'etudiant' && $payment->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            // Vérifier que le paiement peut être annulé
            if (!in_array($payment->status, ['pending', 'processing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce paiement ne peut pas être annulé'
                ], 400);
            }

            $payment->update([
                'status' => 'cancelled',
                'failed_at' => now(),
                'failure_reason' => 'Annulé par l\'utilisateur'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Paiement annulé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation du paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation du paiement'
            ], 500);
        }
    }

    /**
     * Obtenir les méthodes de paiement disponibles
     */
    public function paymentMethods()
    {
        try {
            $methods = PaymentMethod::active()->ordered()->get();

            return response()->json([
                'success' => true,
                'data' => $methods
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des méthodes de paiement'
            ], 500);
        }
    }

    /**
     * Statistiques des paiements (Admin uniquement)
     */
    public function statistics(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $stats = $this->paymentService->getPaymentStatistics($request->all());

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Télécharger le reçu de paiement
     */
    public function downloadReceipt($id)
    {
        try {
            $user = Auth::user();
            $payment = Payment::with(['user', 'enrollement.filiere', 'invoice'])->findOrFail($id);

            // Vérifier les permissions
            if ($user->role === 'etudiant' && $payment->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            // Vérifier que le paiement est réussi
            if ($payment->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Le reçu n\'est disponible que pour les paiements réussis'
                ], 400);
            }

            $pdf = $this->paymentService->generateReceipt($payment);

            return $pdf->download("recu-paiement-{$payment->payment_reference}.pdf");

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du reçu: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement du reçu'
            ], 500);
        }
    }

    /**
     * L'étudiant confirme qu'il a effectué le paiement réel
     */
    public function studentConfirm(Request $request, $id)
    {
        $request->validate([
            'transaction_id' => 'required|string|max:255',
            'payment_method_details' => 'nullable|array',
            'confirmation_notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $payment = Payment::findOrFail($id);

            // Vérifier que c'est le paiement de l'étudiant
            if ($user->role === 'etudiant' && $payment->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            // Confirmer le paiement côté étudiant
            $result = $this->paymentService->studentConfirmPayment($payment, [
                'transaction_id' => $request->transaction_id,
                'external_reference' => $request->external_reference,
                'payment_method_details' => $request->payment_method_details ?? [],
                'confirmation_notes' => $request->confirmation_notes,
                'confirmed_by_user' => $user->id,
                'confirmed_by_name' => $user->name,
                'confirmation_date' => now()->toISOString()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement confirmé avec succès. En attente de vérification par l\'administration.',
                'data' => $payment->fresh()->load(['paymentMethod', 'invoice', 'enrollement'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la confirmation étudiant du paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation du paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * L'admin vérifie un paiement confirmé par l'étudiant
     */
    public function adminVerify(Request $request, $id)
    {
        $request->validate([
            'verified' => 'required|boolean',
            'verification_notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $payment = Payment::with(['user', 'enrollement'])->findOrFail($id);

            // Vérifier le paiement
            $result = $this->paymentService->adminVerifyPayment(
                $payment, 
                $request->verified, 
                $request->verification_notes
            );

            DB::commit();

            $message = $request->verified 
                ? 'Paiement vérifié et approuvé. Le matricule et le quitus ont été générés automatiquement.'
                : 'Paiement contesté. L\'étudiant a été notifié.';

            Log::info("Paiement vérifié par l'admin", [
                'payment_id' => $payment->id,
                'admin_id' => $user->id,
                'verified' => $request->verified,
                'enrollement_id' => $payment->enrollement_id
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $payment->fresh()->load(['paymentMethod', 'invoice', 'enrollement', 'user'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la vérification admin du paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste des paiements en attente de vérification (Admin)
     */
    public function pendingVerification()
    {
        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $pendingPayments = Payment::with(['user', 'enrollement.filiere', 'enrollement.niveau', 'paymentMethod'])
                ->where('verification_status', 'awaiting_verification')
                ->orderBy('student_confirmed_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $pendingPayments,
                'count' => $pendingPayments->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des paiements en attente de vérification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paiements en attente de vérification'
            ], 500);
        }
    }

    /**
     * Admin approuve un paiement en attente (MÉTHODE LEGACY - DÉPRÉCIÉE)
     * @deprecated Utiliser adminVerify à la place
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $payment = Payment::with(['user', 'enrollement'])->findOrFail($id);

            // Vérifier que le paiement peut être approuvé
            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce paiement ne peut pas être approuvé (statut actuel: ' . $payment->status . ')'
                ], 400);
            }

            // Confirmer le paiement avec les détails d'approbation admin
            $result = $this->paymentService->confirmPayment($payment, [
                'transaction_id' => 'ADMIN_APPROVED_' . time(),
                'external_reference' => 'ADMIN_' . $user->id,
                'payment_details' => [
                    'approved_by_admin' => true,
                    'admin_id' => $user->id,
                    'admin_name' => $user->full_name,
                    'admin_notes' => $request->admin_notes,
                    'approval_date' => now()->toISOString()
                ]
            ]);

            DB::commit();

            Log::info("Paiement approuvé par l'admin", [
                'payment_id' => $payment->id,
                'admin_id' => $user->id,
                'enrollement_id' => $payment->enrollement_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Paiement approuvé avec succès. Le matricule et le quitus ont été générés automatiquement.',
                'data' => $payment->fresh()->load(['paymentMethod', 'invoice', 'enrollement', 'user'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'approbation du paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'approbation du paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin rejette un paiement en attente (MÉTHODE LEGACY - DÉPRÉCIÉE)
     * @deprecated Utiliser adminVerify à la place
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $payment = Payment::with(['user', 'enrollement'])->findOrFail($id);

            // Vérifier que le paiement peut être rejeté
            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce paiement ne peut pas être rejeté (statut actuel: ' . $payment->status . ')'
                ], 400);
            }

            // Rejeter le paiement
            $result = $this->paymentService->failPayment($payment, $request->rejection_reason);

            // Ajouter les détails de rejet par l'admin
            $payment->update([
                'payment_details' => array_merge(
                    $payment->payment_details ?? [],
                    [
                        'rejected_by_admin' => true,
                        'admin_id' => $user->id,
                        'admin_name' => $user->full_name,
                        'rejection_reason' => $request->rejection_reason,
                        'rejection_date' => now()->toISOString()
                    ]
                )
            ]);

            Log::info("Paiement rejeté par l'admin", [
                'payment_id' => $payment->id,
                'admin_id' => $user->id,
                'reason' => $request->rejection_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Paiement rejeté. L\'étudiant a été notifié.',
                'data' => $payment->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du rejet du paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet du paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste des paiements en attente d'approbation (Admin)
     */
    public function pendingApprovals()
    {
        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $pendingPayments = Payment::with(['user', 'enrollement.filiere', 'enrollement.niveau', 'paymentMethod'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $pendingPayments,
                'count' => $pendingPayments->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des paiements en attente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paiements en attente'
            ], 500);
        }
    }

    /**
     * Télécharger le reçu d'un paiement (Admin)
     */
    public function downloadReceiptAdmin($id)
    {
        try {
            $payment = Payment::with(['user', 'enrollement', 'paymentMethod'])->findOrFail($id);

            // Vérifier que le paiement est réussi
            if ($payment->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Le reçu n\'est disponible que pour les paiements réussis'
                ], 400);
            }

            Log::info('Admin télécharge le reçu du paiement: ' . $id);

            $pdf = $this->paymentService->generateReceipt($payment);

            return $pdf->download("recu-paiement-{$payment->payment_reference}.pdf");

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du reçu (admin): ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement du reçu'
            ], 500);
        }
    }

    /**
     * Télécharger un reçu détaillé avec QR code
     */
    public function downloadDetailedReceipt($id)
    {
        try {
            $user = Auth::user();
            $payment = Payment::with([
                'user', 
                'paymentMethod', 
                'enrollement.filiere.departement', 
                'enrollement.niveau', 
                'enrollement.academicYear',
                'invoice'
            ])->findOrFail($id);

            // Vérifier les permissions
            if ($user->role === 'etudiant' && $payment->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            // Vérifier que le paiement est vérifié
            if ($payment->status !== 'completed' || $payment->verification_status !== 'verified') {
                return response()->json([
                    'success' => false,
                    'message' => 'Le reçu détaillé n\'est disponible que pour les paiements vérifiés'
                ], 400);
            }

            $pdf = $this->paymentService->generateDetailedReceipt($payment);

            return $pdf->download("recu-detaille-{$payment->payment_reference}.pdf");

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du reçu détaillé: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement du reçu détaillé'
            ], 500);
        }
    }

    /**
     * Télécharger l'historique complet des paiements
     */
    public function downloadPaymentHistory()
    {
        try {
            $user = Auth::user();
            
            if ($user->role === 'etudiant') {
                $payments = Payment::with([
                    'paymentMethod', 
                    'enrollement.filiere.departement', 
                    'enrollement.niveau',
                    'invoice'
                ])
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                // Admin peut télécharger l'historique de tous les paiements
                $payments = Payment::with([
                    'user',
                    'paymentMethod', 
                    'enrollement.filiere.departement', 
                    'enrollement.niveau',
                    'invoice'
                ])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            $pdf = $this->paymentService->generatePaymentHistory($user, $payments);

            $filename = $user->role === 'admin' 
                ? "historique-paiements-global.pdf"
                : "historique-paiements-{$user->id}.pdf";

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement de l\'historique: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement de l\'historique des paiements'
            ], 500);
        }
    }

    /**
     * Télécharger un résumé des paiements avec statistiques
     */
    public function downloadPaymentSummary()
    {
        try {
            $user = Auth::user();
            
            if ($user->role === 'etudiant') {
                $payments = Payment::with([
                    'paymentMethod', 
                    'enrollement.filiere.departement', 
                    'enrollement.niveau',
                    'invoice'
                ])
                    ->where('user_id', $user->id)
                    ->get();
            } else {
                // Admin peut télécharger le résumé global
                $payments = Payment::with([
                    'user',
                    'paymentMethod', 
                    'enrollement.filiere.departement', 
                    'enrollement.niveau',
                    'invoice'
                ])
                    ->get();
            }

            $pdf = $this->paymentService->generatePaymentSummary($user, $payments);

            $filename = $user->role === 'admin' 
                ? "resume-paiements-global.pdf"
                : "resume-paiements-{$user->id}.pdf";

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du résumé: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement du résumé des paiements'
            ], 500);
        }
    }

    /**
     * Télécharger un certificat de paiement officiel
     */
    public function downloadPaymentCertificate($id)
    {
        try {
            $user = Auth::user();
            $payment = Payment::with([
                'user', 
                'paymentMethod', 
                'enrollement.filiere.departement', 
                'enrollement.niveau', 
                'enrollement.academicYear',
                'invoice'
            ])->findOrFail($id);

            // Vérifier les permissions
            if ($user->role === 'etudiant' && $payment->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            // Vérifier que le paiement est vérifié
            if ($payment->status !== 'completed' || $payment->verification_status !== 'verified') {
                return response()->json([
                    'success' => false,
                    'message' => 'Le certificat n\'est disponible que pour les paiements vérifiés'
                ], 400);
            }

            $pdf = $this->paymentService->generatePaymentCertificate($payment);

            return $pdf->download("certificat-paiement-{$payment->payment_reference}.pdf");

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du certificat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement du certificat de paiement'
            ], 500);
        }
    }

    /**
     * Télécharger un rapport de paiements par période (Admin)
     */
    public function downloadPaymentReport(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $request->validate([
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'status' => 'nullable|in:pending,completed,failed,cancelled',
                'payment_method_id' => 'nullable|exists:payment_methods,id'
            ]);

            $query = Payment::with([
                'user',
                'paymentMethod', 
                'enrollement.filiere.departement', 
                'enrollement.niveau',
                'invoice'
            ]);

            // Appliquer les filtres
            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->payment_method_id) {
                $query->where('payment_method_id', $request->payment_method_id);
            }

            $payments = $query->orderBy('created_at', 'desc')->get();

            $pdf = $this->paymentService->generatePaymentReport($payments, $request->all());

            $filename = "rapport-paiements-" . date('Y-m-d') . ".pdf";

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du rapport: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement du rapport de paiements'
            ], 500);
        }
    }
}