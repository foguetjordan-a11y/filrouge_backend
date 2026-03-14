<?php

namespace App\Http\Controllers;

use App\Models\Quitus;
use App\Models\Enrollement;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Notifications\QuitusDisponibleNotification;

class QuitusController extends Controller
{
    /**
     * Générer automatiquement un quitus après paiement confirmé
     */
    public function generateForEnrollment($enrollmentId)
    {
        try {
            $enrollement = Enrollement::with(['etudiant', 'filiere.departement', 'niveau', 'anneeAcademique', 'successfulPayment'])
                ->findOrFail($enrollmentId);
            
            // Vérifier que l'enrôlement est validé
            if ($enrollement->statut !== 'valide') {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'enrôlement doit être validé pour générer un quitus'
                ], 400);
            }

            // Vérifier que le paiement est effectué (nouveau workflow)
            if ($enrollement->requiresPayment() && !$enrollement->isPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le paiement doit être effectué avant la génération du quitus'
                ], 400);
            }
            
            // Vérifier si un quitus existe déjà pour cet utilisateur et cette année
            $existingQuitus = Quitus::where('user_id', $enrollement->user_id)
                ->where('annee_academique_id', $enrollement->annee_academique_id)
                ->first();
            
            if ($existingQuitus) {
                return response()->json([
                    'success' => true,
                    'message' => 'Quitus déjà existant',
                    'data' => $existingQuitus
                ]);
            }
            
            // Créer le quitus
            $reference = 'QT-' . date('Y') . '-' . str_pad($enrollement->user_id, 4, '0', STR_PAD_LEFT) . '-' . strtoupper(Str::random(4));
            
            $quitus = Quitus::create([
                'user_id' => $enrollement->user_id,
                'enrollement_id' => $enrollement->id,
                'annee_academique_id' => $enrollement->annee_academique_id,
                'reference' => $reference,
                'date_emission' => now(),
                'statut' => 'valide'
            ]);
            
            // Générer le PDF
            $this->generatePDF($quitus);
            
            // Notifier l'étudiant
            $enrollement->etudiant->notify(new QuitusDisponibleNotification($quitus));
            
            return response()->json([
                'success' => true,
                'message' => 'Quitus généré avec succès après confirmation du paiement',
                'data' => $quitus
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du quitus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer le quitus après paiement confirmé (appelé automatiquement)
     */
    public function generateAfterPayment($enrollmentId)
    {
        return $this->generateForEnrollment($enrollmentId);
    }
    
    /**
     * Télécharger le quitus (ETUDIANT)
     */
    public function download(Request $request)
    {
        try {
            $user = $request->user();
            
            // Récupérer le quitus de l'étudiant pour l'année académique active
            $quitus = Quitus::where('user_id', $user->id)
                ->with(['user.enrollements.filiere.departement', 'user.enrollements.niveau', 'user.enrollements.anneeAcademique'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$quitus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun quitus disponible. Votre enrôlement doit être validé par l\'administration.'
                ], 404);
            }
            
            // Générer le PDF
            $pdf = $this->generatePDF($quitus);
            
            return $pdf->download('quitus_' . $quitus->reference . '.pdf');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Admin - Télécharger le quitus d'un étudiant
     */
    public function downloadForStudent($userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            $quitus = Quitus::where('user_id', $user->id)
                ->with(['user.enrollements.filiere.departement', 'user.enrollements.niveau', 'user.enrollements.anneeAcademique'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$quitus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun quitus trouvé pour cet étudiant'
                ], 404);
            }
            
            $pdf = $this->generatePDF($quitus);
            
            return $pdf->download('quitus_' . $user->name . '_' . $quitus->reference . '.pdf');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lister tous les quitus (ADMIN)
     */
    public function index()
    {
        try {
            $quitus = Quitus::with(['user', 'enrollement.filiere', 'enrollement.niveau'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $quitus
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des quitus: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Générer le PDF du quitus
     */
    private function generatePDF($quitus)
    {
        $user = $quitus->user;
        
        // Charger les relations nécessaires si pas déjà fait
        if (!$user->relationLoaded('enrollements')) {
            $user->load(['enrollements.filiere.departement', 'enrollements.niveau', 'enrollements.anneeAcademique']);
        }
        
        $pdf = Pdf::loadView('pdf.quitus', [
            'user' => $user,
            'quitus' => $quitus
        ]);
        
        // Configurer le PDF
        $pdf->setPaper('A4', 'portrait');
        
        // Sauvegarder le PDF sur le serveur
        $filename = 'quitus_' . $quitus->reference . '.pdf';
        $path = 'quitus/' . $filename;
        
        Storage::disk('public')->put($path, $pdf->output());
        
        // Mettre à jour le chemin dans la base de données
        $quitus->update(['pdf_path' => $path]);
        
        return $pdf;
    }
}

