<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Enrollement;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Liste des factures
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Invoice::with(['user', 'enrollement.filiere', 'successfulPayment']);

            // Si étudiant, filtrer ses factures uniquement
            if ($user->role === 'etudiant') {
                $query->where('user_id', $user->id);
            }

            // Filtres
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('overdue') && $request->overdue) {
                $query->overdue();
            }

            $invoices = $query->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $invoices
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des factures: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des factures'
            ], 500);
        }
    }

    /**
     * Détails d'une facture
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $query = Invoice::with(['user', 'enrollement.filiere', 'payments']);

            // Si étudiant, vérifier que c'est sa facture
            if ($user->role === 'etudiant') {
                $query->where('user_id', $user->id);
            }

            $invoice = $query->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $invoice
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Facture non trouvée'
            ], 404);
        }
    }

    /**
     * Générer une facture pour un enrôlement (Admin uniquement)
     */
    public function generate(Request $request)
    {
        $request->validate([
            'enrollement_id' => 'required|exists:enrollements,id'
        ]);

        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $enrollement = Enrollement::findOrFail($request->enrollement_id);

            // Vérifier que l'enrôlement est validé
            if ($enrollement->status !== 'valide') {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'enrôlement doit être validé avant de générer une facture'
                ], 400);
            }

            $invoice = $this->invoiceService->generateForEnrollment($enrollement);

            return response()->json([
                'success' => true,
                'message' => 'Facture générée avec succès',
                'data' => $invoice->load(['user', 'enrollement.filiere'])
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération de la facture: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération de la facture'
            ], 500);
        }
    }

    /**
     * Télécharger une facture en PDF
     */
    public function download($id)
    {
        try {
            $user = Auth::user();
            $invoice = Invoice::with(['user', 'enrollement.filiere'])->findOrFail($id);

            // Vérifier les permissions
            if ($user->role === 'etudiant' && $invoice->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $pdf = $this->invoiceService->generatePDF($invoice);

            return $pdf->download("facture-{$invoice->invoice_number}.pdf");

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement de la facture: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement de la facture'
            ], 500);
        }
    }

    /**
     * Marquer une facture comme envoyée (Admin uniquement)
     */
    public function markAsSent($id)
    {
        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $invoice = Invoice::findOrFail($id);
            $invoice->markAsSent();

            return response()->json([
                'success' => true,
                'message' => 'Facture marquée comme envoyée'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la facture: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la facture'
            ], 500);
        }
    }

    /**
     * Annuler une facture (Admin uniquement)
     */
    public function cancel($id)
    {
        try {
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $invoice = Invoice::findOrFail($id);

            // Vérifier qu'elle n'est pas déjà payée
            if ($invoice->status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Une facture payée ne peut pas être annulée'
                ], 400);
            }

            $invoice->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Facture annulée avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation de la facture: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation de la facture'
            ], 500);
        }
    }
}