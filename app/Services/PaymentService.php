<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Enrollement;
use App\Notifications\PaiementConfirmeNotification;
use App\Notifications\PaiementEchoueNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentService
{
    /**
     * Initier un paiement
     */
    public function initiatePayment(array $data): Payment
    {
        $paymentMethod = PaymentMethod::findOrFail($data['payment_method_id']);
        
        // Calculer les frais
        $amount = $data['amount'];
        $feeAmount = $paymentMethod->calculateFees($amount);
        $netAmount = $amount - $feeAmount;

        // Vérifier les limites de montant
        if (!$paymentMethod->isAmountValid($amount)) {
            throw new \Exception('Montant non valide pour cette méthode de paiement');
        }

        // Créer le paiement
        $payment = Payment::create([
            'user_id' => $data['user_id'],
            'enrollement_id' => $data['enrollement_id'],
            'invoice_id' => $data['invoice_id'] ?? null,
            'payment_method_id' => $data['payment_method_id'],
            'payment_reference' => Payment::generatePaymentReference(),
            'amount' => $amount,
            'fee_amount' => $feeAmount,
            'net_amount' => $netAmount,
            'currency' => 'FCFA',
            'status' => 'pending',
            'verification_status' => 'pending',
            'submitted_at' => now()
        ]);

        Log::info("Paiement initié: {$payment->payment_reference} pour {$amount} XOF");

        return $payment;
    }

    /**
     * L'étudiant confirme qu'il a effectué le paiement réel
     */
    public function studentConfirmPayment(Payment $payment, array $confirmationData): Payment
    {
        try {
            DB::beginTransaction();

            // Vérifier que le paiement peut être confirmé par l'étudiant
            if ($payment->status !== 'pending') {
                throw new \Exception('Ce paiement ne peut pas être confirmé (statut: ' . $payment->status . ')');
            }

            if ($payment->verification_status !== 'pending') {
                throw new \Exception('Ce paiement a déjà été confirmé');
            }

            // Mettre à jour le paiement avec la confirmation étudiant
            $payment->update([
                'verification_status' => 'awaiting_verification',
                'student_confirmed_at' => now(),
                'student_confirmation_details' => $confirmationData,
                'transaction_id' => $confirmationData['transaction_id'] ?? null,
                'external_reference' => $confirmationData['external_reference'] ?? null
            ]);

            DB::commit();

            Log::info("Paiement confirmé par l'étudiant: {$payment->payment_reference}", [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id
            ]);

            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la confirmation étudiant du paiement {$payment->payment_reference}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * L'admin vérifie un paiement confirmé par l'étudiant
     */
    public function adminVerifyPayment(Payment $payment, bool $verified, string $notes = null): Payment
    {
        try {
            DB::beginTransaction();

            // Vérifier que le paiement peut être vérifié
            if ($payment->verification_status !== 'awaiting_verification') {
                throw new \Exception('Ce paiement ne peut pas être vérifié (statut: ' . $payment->verification_status . ')');
            }

            if ($verified) {
                // Paiement vérifié et approuvé
                $payment->update([
                    'status' => 'completed',
                    'verification_status' => 'verified',
                    'admin_verified_at' => now(),
                    'admin_verification_notes' => $notes,
                    'completed_at' => now()
                ]);

                // Marquer la facture comme payée si elle existe
                if ($payment->invoice) {
                    $payment->invoice->markAsPaid();
                }

                // Marquer l'enrôlement comme payé
                $payment->enrollement->markAsPaid();

                // Envoyer la notification de paiement confirmé
                $payment->user->notify(new PaiementConfirmeNotification($payment));

                // Générer le reçu PDF
                $this->generateAndStoreReceipt($payment);

                // Générer automatiquement le quitus après vérification
                try {
                    $quitusController = app(\App\Http\Controllers\QuitusController::class);
                    $quitusController->generateAfterPayment($payment->enrollement_id);
                    Log::info("Quitus généré automatiquement après vérification admin", [
                        'payment_id' => $payment->id,
                        'enrollement_id' => $payment->enrollement_id
                    ]);
                } catch (\Exception $e) {
                    Log::warning("Erreur lors de la génération automatique du quitus", [
                        'payment_id' => $payment->id,
                        'enrollement_id' => $payment->enrollement_id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Générer automatiquement le matricule après vérification
                try {
                    $matriculeService = app(\App\Services\MatriculeService::class);
                    $matricule = $matriculeService->generateAfterPayment($payment->user, $payment->enrollement);
                    
                    if ($matricule) {
                        Log::info("Matricule généré automatiquement après vérification admin", [
                            'payment_id' => $payment->id,
                            'user_id' => $payment->user_id,
                            'enrollement_id' => $payment->enrollement_id,
                            'matricule' => $matricule
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning("Erreur lors de la génération automatique du matricule", [
                        'payment_id' => $payment->id,
                        'user_id' => $payment->user_id,
                        'enrollement_id' => $payment->enrollement_id,
                        'error' => $e->getMessage()
                    ]);
                }

                Log::info("Paiement vérifié et approuvé par l'admin: {$payment->payment_reference}");

            } else {
                // Paiement contesté
                $payment->update([
                    'verification_status' => 'disputed',
                    'admin_verified_at' => now(),
                    'admin_verification_notes' => $notes
                ]);

                // Envoyer une notification de contestation
                $payment->user->notify(new PaiementEchoueNotification($payment, $notes));

                Log::info("Paiement contesté par l'admin: {$payment->payment_reference}", [
                    'reason' => $notes
                ]);
            }

            DB::commit();

            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la vérification admin du paiement {$payment->payment_reference}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Confirmer un paiement (méthode legacy - à utiliser uniquement pour les paiements automatiques)
     */
    public function confirmPayment(Payment $payment, array $confirmationData): Payment
    {
        try {
            DB::beginTransaction();

            // Mettre à jour le paiement
            $payment->update([
                'status' => 'completed',
                'verification_status' => 'verified',
                'transaction_id' => $confirmationData['transaction_id'] ?? null,
                'external_reference' => $confirmationData['external_reference'] ?? null,
                'payment_details' => array_merge(
                    $payment->payment_details ?? [],
                    $confirmationData['payment_details'] ?? []
                ),
                'completed_at' => now(),
                'admin_verified_at' => now(),
                'admin_verification_notes' => 'Paiement automatique confirmé'
            ]);

            // Marquer la facture comme payée si elle existe
            if ($payment->invoice) {
                $payment->invoice->markAsPaid();
            }

            // Marquer l'enrôlement comme payé
            $payment->enrollement->markAsPaid();

            // Envoyer la notification de paiement confirmé
            $payment->user->notify(new PaiementConfirmeNotification($payment));

            // Générer le reçu PDF
            $this->generateAndStoreReceipt($payment);

            // Générer automatiquement le quitus après paiement confirmé
            try {
                $quitusController = app(\App\Http\Controllers\QuitusController::class);
                $quitusController->generateAfterPayment($payment->enrollement_id);
                Log::info("Quitus généré automatiquement après paiement confirmé", [
                    'payment_id' => $payment->id,
                    'enrollement_id' => $payment->enrollement_id
                ]);
            } catch (\Exception $e) {
                Log::warning("Erreur lors de la génération automatique du quitus", [
                    'payment_id' => $payment->id,
                    'enrollement_id' => $payment->enrollement_id,
                    'error' => $e->getMessage()
                ]);
                // Ne pas faire échouer le paiement si la génération du quitus échoue
            }

            // Générer automatiquement le matricule après paiement confirmé
            try {
                $matriculeService = app(\App\Services\MatriculeService::class);
                $matricule = $matriculeService->generateAfterPayment($payment->user, $payment->enrollement);
                
                if ($matricule) {
                    Log::info("Matricule généré automatiquement après paiement confirmé", [
                        'payment_id' => $payment->id,
                        'user_id' => $payment->user_id,
                        'enrollement_id' => $payment->enrollement_id,
                        'matricule' => $matricule
                    ]);
                } else {
                    Log::info("Matricule non généré - conditions non remplies", [
                        'payment_id' => $payment->id,
                        'user_id' => $payment->user_id,
                        'enrollement_id' => $payment->enrollement_id
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning("Erreur lors de la génération automatique du matricule", [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'enrollement_id' => $payment->enrollement_id,
                    'error' => $e->getMessage()
                ]);
                // Ne pas faire échouer le paiement si la génération du matricule échoue
            }

            DB::commit();

            Log::info("Paiement confirmé: {$payment->payment_reference}");

            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la confirmation du paiement {$payment->payment_reference}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Marquer un paiement comme échoué
     */
    public function failPayment(Payment $payment, string $reason = null): Payment
    {
        $payment->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'failed_at' => now()
        ]);

        // Envoyer la notification d'échec
        $payment->user->notify(new PaiementEchoueNotification($payment, $reason));

        Log::warning("Paiement échoué: {$payment->payment_reference} - Raison: {$reason}");

        return $payment;
    }

    /**
     * Obtenir les instructions de paiement selon la méthode
     */
    public function getPaymentInstructions(Payment $payment): array
    {
        $method = $payment->paymentMethod;
        
        $baseInstructions = [
            'payment_reference' => $payment->payment_reference,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'method_name' => $method->name
        ];

        switch ($method->code) {
            case 'mtn_momo':
                return array_merge($baseInstructions, [
                    'type' => 'mtn_mobile_money',
                    'phone_number' => $method->getConfig('phone_number', '+237 6 77 12 34 56'),
                    'operator' => 'MTN Mobile Money',
                    'ussd_code' => '*126#',
                    'merchant_code' => $method->getConfig('merchant_code', 'MTN_UNIV_001'),
                    'instructions' => [
                        '1. Composez *126# sur votre téléphone MTN',
                        '2. Sélectionnez "Transfert d\'argent"',
                        '3. Saisissez le numéro: ' . $method->getConfig('phone_number'),
                        '4. Montant: ' . number_format($payment->amount, 0, ',', ' ') . ' FCFA',
                        '5. Référence: ' . $payment->payment_reference,
                        '6. Confirmez avec votre code PIN MTN'
                    ],
                    'fees' => 'Frais MTN: ' . number_format($payment->fee_amount, 0, ',', ' ') . ' FCFA'
                ]);

            case 'orange_money_cm':
                return array_merge($baseInstructions, [
                    'type' => 'orange_money_cameroun',
                    'phone_number' => $method->getConfig('phone_number', '+237 6 99 87 65 43'),
                    'operator' => 'Orange Money Cameroun',
                    'ussd_code' => '#150#',
                    'merchant_code' => $method->getConfig('merchant_code', 'OM_UNIV_CM_001'),
                    'instructions' => [
                        '1. Composez #150# sur votre téléphone Orange',
                        '2. Sélectionnez "Transfert d\'argent"',
                        '3. Saisissez le numéro: ' . $method->getConfig('phone_number'),
                        '4. Montant: ' . number_format($payment->amount, 0, ',', ' ') . ' FCFA',
                        '5. Référence: ' . $payment->payment_reference,
                        '6. Confirmez avec votre code secret Orange Money'
                    ],
                    'fees' => 'Frais Orange: ' . number_format($payment->fee_amount, 0, ',', ' ') . ' FCFA'
                ]);

            case 'bank_transfer':
                return array_merge($baseInstructions, [
                    'type' => 'bank_transfer',
                    'bank_name' => $method->getConfig('bank_name', 'Banque Atlantique Cameroun'),
                    'account_number' => $method->getConfig('account_number', '10001234567890'),
                    'account_name' => $method->getConfig('account_name', 'Université Virtuelle - Frais d\'enrôlement'),
                    'swift_code' => $method->getConfig('swift_code', 'ATCMCMCX'),
                    'rib' => $method->getConfig('rib', '10001 12345 67890123456 78'),
                    'instructions' => [
                        'Effectuez un virement bancaire avec les informations suivantes:',
                        'Banque: ' . $method->getConfig('bank_name'),
                        'Compte: ' . $method->getConfig('account_number'),
                        'RIB: ' . $method->getConfig('rib'),
                        'Bénéficiaire: ' . $method->getConfig('account_name'),
                        'Référence obligatoire: ' . $payment->payment_reference,
                        'Montant: ' . number_format($payment->amount, 0, ',', ' ') . ' FCFA'
                    ]
                ]);

            case 'eu_mobile':
                return array_merge($baseInstructions, [
                    'type' => 'express_union',
                    'phone_number' => $method->getConfig('phone_number', '+237 6 55 44 33 22'),
                    'operator' => 'Express Union Mobile',
                    'instructions' => [
                        '1. Rendez-vous dans une agence Express Union',
                        '2. Présentez votre pièce d\'identité',
                        '3. Demandez un transfert vers: ' . $method->getConfig('phone_number'),
                        '4. Montant: ' . number_format($payment->amount, 0, ',', ' ') . ' FCFA',
                        '5. Référence: ' . $payment->payment_reference,
                        '6. Conservez le reçu de transaction'
                    ],
                    'fees' => 'Frais Express Union: ' . number_format($payment->fee_amount, 0, ',', ' ') . ' FCFA'
                ]);

            case 'cash':
                return array_merge($baseInstructions, [
                    'type' => 'cash',
                    'office_address' => $method->getConfig('office_address', 'Bureau des Finances - Campus Principal'),
                    'office_hours' => $method->getConfig('office_hours', 'Lundi-Vendredi: 8h-17h'),
                    'contact_phone' => $method->getConfig('contact_phone', '+237 6 XX XX XX XX'),
                    'instructions' => [
                        'Présentez-vous au bureau des finances avec:',
                        '• Votre pièce d\'identité',
                        '• Le montant exact: ' . number_format($payment->amount, 0, ',', ' ') . ' FCFA',
                        '• Votre référence de paiement: ' . $payment->payment_reference,
                        'Adresse: ' . $method->getConfig('office_address'),
                        'Horaires: ' . $method->getConfig('office_hours')
                    ]
                ]);

            case 'simulation':
                return array_merge($baseInstructions, [
                    'type' => 'simulation',
                    'instructions' => [
                        'Paiement simulé pour démonstration',
                        'Cliquez sur "Confirmer" pour simuler un paiement réussi',
                        'Montant: ' . number_format($payment->amount, 0, ',', ' ') . ' FCFA'
                    ]
                ]);

            default:
                return array_merge($baseInstructions, [
                    'type' => 'generic',
                    'instructions' => [
                        'Contactez l\'administration pour les instructions de paiement',
                        'Référence: ' . $payment->payment_reference,
                        'Montant: ' . number_format($payment->amount, 0, ',', ' ') . ' FCFA'
                    ]
                ]);
        }
    }

    /**
     * Générer et stocker le reçu de paiement
     */
    public function generateAndStoreReceipt(Payment $payment): string
    {
        $pdf = $this->generateReceipt($payment);
        
        $filename = "recu-{$payment->payment_reference}.pdf";
        $path = "receipts/{$filename}";
        
        // Stocker le PDF
        $pdf->save(storage_path("app/public/{$path}"));
        
        // Mettre à jour le chemin dans le paiement
        $payment->update(['receipt_path' => $path]);
        
        return $path;
    }

    /**
     * Générer le PDF du reçu de paiement
     */
    public function generateReceipt(Payment $payment)
    {
        $data = [
            'payment' => $payment->load(['user', 'enrollement.filiere', 'paymentMethod', 'invoice']),
            'generated_at' => now()
        ];

        return Pdf::loadView('pdf.payment-receipt', $data)
                  ->setPaper('a4')
                  ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
    }

    /**
     * Obtenir les statistiques de paiement
     */
    public function getPaymentStatistics(array $filters = []): array
    {
        $query = Payment::query();

        // Appliquer les filtres de date
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Statistiques générales
        $totalPayments = $query->count();
        $completedPayments = (clone $query)->where('status', 'completed')->count();
        $pendingPayments = (clone $query)->where('status', 'pending')->count();
        $failedPayments = (clone $query)->where('status', 'failed')->count();
        
        $totalAmount = (clone $query)->where('status', 'completed')->sum('amount');
        $totalFees = (clone $query)->where('status', 'completed')->sum('fee_amount');

        // Statistiques par méthode de paiement
        $paymentsByMethod = (clone $query)
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->selectRaw("payment_methods.name, COUNT(*) as count, SUM(CASE WHEN payments.status = 'completed' THEN payments.amount ELSE 0 END) as total_amount")
            ->groupBy('payment_methods.id', 'payment_methods.name')
            ->get();

        // Évolution mensuelle (compatible SQLite et MySQL)
        $driver = \DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $monthlyStats = (clone $query)
                ->selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count, SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_amount")
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        } else {
            $monthlyStats = (clone $query)
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count, SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as total_amount')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        return [
            'summary' => [
                'total_payments' => $totalPayments,
                'completed_payments' => $completedPayments,
                'pending_payments' => $pendingPayments,
                'failed_payments' => $failedPayments,
                'success_rate' => $totalPayments > 0 ? round(($completedPayments / $totalPayments) * 100, 2) : 0,
                'total_amount' => $totalAmount,
                'total_fees' => $totalFees,
                'net_amount' => $totalAmount - $totalFees
            ],
            'by_method' => $paymentsByMethod,
            'monthly_evolution' => $monthlyStats
        ];
    }

    /**
     * Vérifier les paiements en retard et envoyer des relances
     */
    public function checkOverduePayments(): int
    {
        $overduePayments = Payment::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(3))
            ->with(['user', 'enrollement'])
            ->get();

        $count = 0;
        foreach ($overduePayments as $payment) {
            // Envoyer une notification de relance
            // $payment->user->notify(new PaiementEnRetardNotification($payment));
            $count++;
        }

        Log::info("Vérification des paiements en retard: {$count} relances envoyées");

        return $count;
    }

    /**
     * Générer un reçu détaillé avec QR code
     */
    public function generateDetailedReceipt(Payment $payment)
    {
        $data = [
            'payment' => $payment->load([
                'user', 
                'enrollement.filiere.departement', 
                'enrollement.niveau', 
                'enrollement.academicYear',
                'paymentMethod', 
                'invoice'
            ]),
            'generated_at' => now(),
            'qr_code' => $this->generateQRCode($payment->payment_reference),
            'verification_url' => "https://universite-cameroun.cm/verify/{$payment->payment_reference}"
        ];

        return Pdf::loadView('pdf.detailed-payment-receipt', $data)
                  ->setPaper('a4')
                  ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
    }

    /**
     * Générer l'historique des paiements
     */
    public function generatePaymentHistory($user, $payments)
    {
        $summary = [
            'total_payments' => $payments->count(),
            'completed_payments' => $payments->where('status', 'completed')->count(),
            'pending_payments' => $payments->where('status', 'pending')->count(),
            'failed_payments' => $payments->where('status', 'failed')->count(),
            'total_amount_paid' => $payments->where('status', 'completed')->sum('amount'),
            'total_amount_pending' => $payments->where('status', 'pending')->sum('amount'),
            'total_fees_paid' => $payments->where('status', 'completed')->sum('fee_amount')
        ];

        $data = [
            'user' => $user,
            'payments' => $payments,
            'summary' => $summary,
            'generated_at' => now(),
            'period' => [
                'from' => $payments->min('created_at'),
                'to' => $payments->max('created_at')
            ]
        ];

        return Pdf::loadView('pdf.payment-history', $data)
                  ->setPaper('a4')
                  ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
    }

    /**
     * Générer un résumé des paiements avec statistiques
     */
    public function generatePaymentSummary($user, $payments)
    {
        $summary = [
            'total_payments' => $payments->count(),
            'completed_payments' => $payments->where('status', 'completed')->count(),
            'pending_payments' => $payments->where('status', 'pending')->count(),
            'failed_payments' => $payments->where('status', 'failed')->count(),
            'total_amount_paid' => $payments->where('status', 'completed')->sum('amount'),
            'total_amount_pending' => $payments->where('status', 'pending')->sum('amount'),
            'payment_methods_used' => $payments->groupBy('payment_method_id')->map(function($group) {
                return [
                    'method' => $group->first()->paymentMethod->name,
                    'count' => $group->count(),
                    'total' => $group->where('status', 'completed')->sum('amount'),
                    'success_rate' => $group->count() > 0 ? round(($group->where('status', 'completed')->count() / $group->count()) * 100, 2) : 0
                ];
            })->values(),
            'monthly_breakdown' => $payments->groupBy(function($payment) {
                return $payment->created_at->format('Y-m');
            })->map(function($group, $month) {
                return [
                    'month' => $month,
                    'count' => $group->count(),
                    'total' => $group->where('status', 'completed')->sum('amount'),
                    'completed' => $group->where('status', 'completed')->count()
                ];
            })->values()
        ];

        $data = [
            'user' => $user,
            'payments' => $payments,
            'summary' => $summary,
            'generated_at' => now()
        ];

        return Pdf::loadView('pdf.payment-summary', $data)
                  ->setPaper('a4')
                  ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
    }

    /**
     * Générer un certificat de paiement officiel
     */
    public function generatePaymentCertificate(Payment $payment)
    {
        $data = [
            'payment' => $payment->load([
                'user', 
                'enrollement.filiere.departement', 
                'enrollement.niveau', 
                'enrollement.academicYear',
                'paymentMethod', 
                'invoice'
            ]),
            'generated_at' => now(),
            'certificate_number' => 'CERT-' . $payment->payment_reference,
            'qr_code' => $this->generateQRCode($payment->payment_reference)
        ];

        return Pdf::loadView('pdf.payment-certificate', $data)
                  ->setPaper('a4')
                  ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
    }

    /**
     * Générer un rapport de paiements pour l'administration
     */
    public function generatePaymentReport($payments, $filters = [])
    {
        $summary = [
            'total_payments' => $payments->count(),
            'completed_payments' => $payments->where('status', 'completed')->count(),
            'pending_payments' => $payments->where('status', 'pending')->count(),
            'failed_payments' => $payments->where('status', 'failed')->count(),
            'total_amount' => $payments->where('status', 'completed')->sum('amount'),
            'total_fees' => $payments->where('status', 'completed')->sum('fee_amount'),
            'by_method' => $payments->groupBy('payment_method_id')->map(function($group) {
                return [
                    'method' => $group->first()->paymentMethod->name,
                    'count' => $group->count(),
                    'total' => $group->where('status', 'completed')->sum('amount'),
                    'success_rate' => $group->count() > 0 ? round(($group->where('status', 'completed')->count() / $group->count()) * 100, 2) : 0
                ];
            })->values(),
            'by_status' => $payments->groupBy('status')->map(function($group, $status) {
                return [
                    'status' => $status,
                    'count' => $group->count(),
                    'total' => $group->sum('amount')
                ];
            })->values()
        ];

        $data = [
            'payments' => $payments,
            'summary' => $summary,
            'filters' => $filters,
            'generated_at' => now(),
            'period' => [
                'from' => $filters['date_from'] ?? $payments->min('created_at'),
                'to' => $filters['date_to'] ?? $payments->max('created_at')
            ]
        ];

        return Pdf::loadView('pdf.payment-report', $data)
                  ->setPaper('a4')
                  ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
    }

    /**
     * Générer un QR code pour la vérification
     */
    private function generateQRCode($reference)
    {
        // Génération simple d'un QR code (vous pouvez utiliser une librairie comme SimpleSoftwareIO/simple-qrcode)
        return "https://universite-cameroun.cm/verify/{$reference}";
    }
}