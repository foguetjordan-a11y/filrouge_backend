<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Enrollement;
use App\Notifications\FactureGenereeNotification;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    /**
     * Générer une facture pour un enrôlement
     */
    public function generateForEnrollment(Enrollement $enrollement): Invoice
    {
        // Vérifier s'il existe déjà une facture pour cet enrôlement
        $existingInvoice = Invoice::where('enrollement_id', $enrollement->id)->first();
        if ($existingInvoice) {
            return $existingInvoice;
        }

        // Calculer les montants
        $amounts = $this->calculateAmountsForEnrollment($enrollement);

        // Créer la facture
        $invoice = Invoice::create([
            'user_id' => $enrollement->user_id,
            'enrollement_id' => $enrollement->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'title' => "Frais d'enrôlement - {$enrollement->filiere->nom}",
            'description' => $this->generateInvoiceDescription($enrollement),
            'subtotal' => $amounts['subtotal'],
            'tax_amount' => $amounts['tax_amount'],
            'total_amount' => $amounts['total_amount'],
            'currency' => 'XOF',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'draft',
            'line_items' => $amounts['line_items']
        ]);

        // Marquer comme envoyée et envoyer la notification
        $invoice->markAsSent();
        $enrollement->user->notify(new FactureGenereeNotification($invoice));

        Log::info("Facture générée: {$invoice->invoice_number} pour l'enrôlement {$enrollement->id}");

        return $invoice;
    }

    /**
     * Obtenir ou créer une facture pour un enrôlement
     */
    public function getOrCreateForEnrollment(Enrollement $enrollement): Invoice
    {
        $invoice = Invoice::where('enrollement_id', $enrollement->id)->first();
        
        if (!$invoice) {
            $invoice = $this->generateForEnrollment($enrollement);
        }

        return $invoice;
    }

    /**
     * Calculer les montants pour un enrôlement
     */
    private function calculateAmountsForEnrollment(Enrollement $enrollement): array
    {
        $lineItems = [];
        $subtotal = 0;

        // Frais d'enrôlement de base
        $enrollmentFee = $this->getEnrollmentFee($enrollement);
        $lineItems[] = [
            'description' => 'Frais d\'enrôlement',
            'quantity' => 1,
            'unit_price' => $enrollmentFee,
            'total' => $enrollmentFee
        ];
        $subtotal += $enrollmentFee;

        // Frais de dossier
        $fileFee = $this->getFileFee($enrollement);
        if ($fileFee > 0) {
            $lineItems[] = [
                'description' => 'Frais de dossier',
                'quantity' => 1,
                'unit_price' => $fileFee,
                'total' => $fileFee
            ];
            $subtotal += $fileFee;
        }

        // Frais de quitus
        $quitusFee = $this->getQuitusFee($enrollement);
        if ($quitusFee > 0) {
            $lineItems[] = [
                'description' => 'Frais de quitus',
                'quantity' => 1,
                'unit_price' => $quitusFee,
                'total' => $quitusFee
            ];
            $subtotal += $quitusFee;
        }

        // Frais spécifiques à la filière (si applicable)
        $specialFees = $this->getSpecialFees($enrollement);
        foreach ($specialFees as $fee) {
            $lineItems[] = $fee;
            $subtotal += $fee['total'];
        }

        // Calculer les taxes (TVA par exemple, si applicable)
        $taxRate = 0; // Pas de TVA sur les frais d'éducation généralement
        $taxAmount = ($subtotal * $taxRate) / 100;
        $totalAmount = $subtotal + $taxAmount;

        return [
            'line_items' => $lineItems,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount
        ];
    }

    /**
     * Obtenir les frais d'enrôlement selon la filière et le niveau (Cameroun - FCFA)
     */
    private function getEnrollmentFee(Enrollement $enrollement): float
    {
        // Frais de base selon le niveau (en FCFA - Cameroun)
        $baseFees = [
            'L1' => 75000,  // 75,000 FCFA
            'L2' => 80000,  // 80,000 FCFA
            'L3' => 85000,  // 85,000 FCFA
            'M1' => 100000, // 100,000 FCFA
            'M2' => 110000, // 110,000 FCFA
        ];

        $niveauCode = $enrollement->niveau->code ?? 'L1';
        $baseFee = $baseFees[$niveauCode] ?? $baseFees['L1'];

        // Ajustement selon la filière (contexte camerounais)
        $filiereMultiplier = 1.0;
        $filiereNom = strtolower($enrollement->filiere->nom);
        
        if (str_contains($filiereNom, 'ingénieur') || str_contains($filiereNom, 'informatique')) {
            $filiereMultiplier = 1.3; // 30% de plus pour les filières techniques
        } elseif (str_contains($filiereNom, 'médecine') || str_contains($filiereNom, 'santé')) {
            $filiereMultiplier = 1.6; // 60% de plus pour médecine/santé
        } elseif (str_contains($filiereNom, 'droit') || str_contains($filiereNom, 'économie')) {
            $filiereMultiplier = 1.1; // 10% de plus pour droit/économie
        } elseif (str_contains($filiereNom, 'gestion') || str_contains($filiereNom, 'management')) {
            $filiereMultiplier = 1.2; // 20% de plus pour gestion/management
        }

        return $baseFee * $filiereMultiplier;
    }

    /**
     * Obtenir les frais de dossier (Cameroun)
     */
    private function getFileFee(Enrollement $enrollement): float
    {
        return 15000; // 15,000 FCFA
    }

    /**
     * Obtenir les frais de quitus (Cameroun)
     */
    private function getQuitusFee(Enrollement $enrollement): float
    {
        return 10000; // 10,000 FCFA
    }

    /**
     * Obtenir les frais spéciaux selon la filière (Cameroun)
     */
    private function getSpecialFees(Enrollement $enrollement): array
    {
        $specialFees = [];
        $filiereNom = strtolower($enrollement->filiere->nom);

        // Frais de laboratoire pour certaines filières
        if (str_contains($filiereNom, 'informatique') ||
            str_contains($filiereNom, 'ingénieur') ||
            str_contains($filiereNom, 'sciences')) {
            $specialFees[] = [
                'description' => 'Frais de laboratoire et équipements',
                'quantity' => 1,
                'unit_price' => 20000,
                'total' => 20000
            ];
        }

        // Frais de stage pour les niveaux avancés
        if (in_array($enrollement->niveau->code ?? '', ['L3', 'M1', 'M2'])) {
            $specialFees[] = [
                'description' => 'Frais d\'encadrement de stage',
                'quantity' => 1,
                'unit_price' => 15000,
                'total' => 15000
            ];
        }

        // Frais de bibliothèque numérique
        $specialFees[] = [
            'description' => 'Accès bibliothèque numérique',
            'quantity' => 1,
            'unit_price' => 5000,
            'total' => 5000
        ];

        // Frais spéciaux pour médecine
        if (str_contains($filiereNom, 'médecine') || str_contains($filiereNom, 'santé')) {
            $specialFees[] = [
                'description' => 'Frais de matériel médical',
                'quantity' => 1,
                'unit_price' => 25000,
                'total' => 25000
            ];
        }

        return $specialFees;
    }

    /**
     * Générer la description de la facture
     */
    private function generateInvoiceDescription(Enrollement $enrollement): string
    {
        return sprintf(
            "Facture pour l'enrôlement de %s %s en %s (%s) pour l'année académique %s",
            $enrollement->user->prenom,
            $enrollement->user->nom,
            $enrollement->filiere->nom,
            $enrollement->niveau->nom ?? 'Niveau non spécifié',
            $enrollement->academic_year ?? date('Y') . '-' . (date('Y') + 1)
        );
    }

    /**
     * Générer le PDF de la facture
     */
    public function generatePDF(Invoice $invoice)
    {
        $data = [
            'invoice' => $invoice->load(['user', 'enrollement.filiere', 'enrollement.niveau']),
            'generated_at' => now()
        ];

        return Pdf::loadView('pdf.invoice', $data)
                  ->setPaper('a4')
                  ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
    }

    /**
     * Envoyer une facture par email
     */
    public function sendInvoiceByEmail(Invoice $invoice): bool
    {
        try {
            // Générer le PDF
            $pdf = $this->generatePDF($invoice);
            
            // Envoyer par email avec le PDF en pièce jointe
            $invoice->user->notify(new FactureGenereeNotification($invoice, $pdf));
            
            // Marquer comme envoyée
            $invoice->markAsSent();
            
            Log::info("Facture {$invoice->invoice_number} envoyée par email à {$invoice->user->email}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de la facture {$invoice->invoice_number}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier les factures en retard
     */
    public function checkOverdueInvoices(): int
    {
        $overdueInvoices = Invoice::overdue()->with(['user'])->get();
        
        $count = 0;
        foreach ($overdueInvoices as $invoice) {
            // Marquer comme en retard si pas déjà fait
            if ($invoice->status !== 'overdue') {
                $invoice->update(['status' => 'overdue']);
            }
            
            // Envoyer une notification de relance
            // $invoice->user->notify(new FactureEnRetardNotification($invoice));
            $count++;
        }

        Log::info("Vérification des factures en retard: {$count} factures en retard trouvées");

        return $count;
    }
}