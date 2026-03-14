<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'enrollement_id',
        'invoice_id',
        'payment_method_id',
        'payment_reference',
        'amount',
        'fee_amount',
        'net_amount',
        'currency',
        'status',
        'transaction_id',
        'external_reference',
        'payment_details',
        'notes',
        'receipt_path',
        'submitted_at',
        'processed_at',
        'completed_at',
        'failed_at',
        'failure_reason',
        // Nouveaux champs pour le workflow corrigé
        'student_confirmed_at',
        'student_confirmation_details',
        'admin_verified_at',
        'admin_verification_notes',
        'verification_status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payment_details' => 'array',
        'student_confirmation_details' => 'array',
        'submitted_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'student_confirmed_at' => 'datetime',
        'admin_verified_at' => 'datetime'
    ];

    /**
     * Relation avec l'utilisateur (étudiant)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec l'enrôlement
     */
    public function enrollement()
    {
        return $this->belongsTo(Enrollement::class);
    }

    /**
     * Relation avec la facture
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Relation avec la méthode de paiement
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Scope pour les paiements en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les paiements réussis
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope pour les paiements échoués
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope pour les paiements en attente de vérification
     */
    public function scopeAwaitingVerification($query)
    {
        return $query->where('verification_status', 'awaiting_verification');
    }

    /**
     * Scope pour les paiements vérifiés
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    /**
     * Scope pour les paiements contestés
     */
    public function scopeDisputed($query)
    {
        return $query->where('verification_status', 'disputed');
    }

    /**
     * Vérifier si le paiement est réussi
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifier si le paiement est en attente
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Vérifier si le paiement a échoué
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Marquer le paiement comme réussi
     */
    public function markAsCompleted(string $transactionId = null)
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'completed_at' => now()
        ]);

        // Marquer la facture comme payée si elle existe
        if ($this->invoice) {
            $this->invoice->markAsPaid();
        }
    }

    /**
     * Marquer le paiement comme échoué
     */
    public function markAsFailed(string $reason = null)
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'failed_at' => now()
        ]);
    }

    /**
     * Marquer le paiement comme en cours de traitement
     */
    public function markAsProcessing()
    {
        $this->update([
            'status' => 'processing',
            'processed_at' => now()
        ]);
    }

    /**
     * Générer une référence de paiement unique
     */
    public static function generatePaymentReference(): string
    {
        $year = date('Y');
        $lastPayment = self::where('payment_reference', 'like', "PAY-{$year}-%")
                          ->orderBy('payment_reference', 'desc')
                          ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_reference, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('PAY-%s-%04d', $year, $newNumber);
    }

    /**
     * Obtenir le statut formaté
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'processing' => 'En cours',
            'completed' => 'Réussi',
            'failed' => 'Échoué',
            'cancelled' => 'Annulé',
            'refunded' => 'Remboursé',
            default => 'Inconnu'
        };
    }

    /**
     * Obtenir les détails de paiement pour un paramètre spécifique
     */
    public function getPaymentDetail(string $key, $default = null)
    {
        return $this->payment_details[$key] ?? $default;
    }
}