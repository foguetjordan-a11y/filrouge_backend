<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'enrollement_id',
        'invoice_number',
        'title',
        'description',
        'subtotal',
        'tax_amount',
        'total_amount',
        'currency',
        'issue_date',
        'due_date',
        'status',
        'pdf_path',
        'line_items',
        'notes',
        'sent_at',
        'paid_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'line_items' => 'array',
        'issue_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime'
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
     * Relation avec les paiements
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Obtenir le paiement réussi (s'il existe)
     */
    public function successfulPayment()
    {
        return $this->hasOne(Payment::class)->where('status', 'completed');
    }

    /**
     * Scope pour les factures en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope pour les factures payées
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope pour les factures en retard
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'sent')
                    ->where('due_date', '<', now());
    }

    /**
     * Vérifier si la facture est en retard
     */
    public function isOverdue(): bool
    {
        return $this->status === 'sent' && $this->due_date < now();
    }

    /**
     * Vérifier si la facture est payée
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Marquer la facture comme payée
     */
    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);
    }

    /**
     * Marquer la facture comme envoyée
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    /**
     * Générer un numéro de facture unique
     */
    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $lastInvoice = self::where('invoice_number', 'like', "INV-{$year}-%")
                          ->orderBy('invoice_number', 'desc')
                          ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('INV-%s-%03d', $year, $newNumber);
    }

    /**
     * Obtenir le montant restant à payer
     */
    public function getRemainingAmount(): float
    {
        $paidAmount = $this->payments()
                          ->where('status', 'completed')
                          ->sum('amount');

        return max(0, $this->total_amount - $paidAmount);
    }

    /**
     * Obtenir le statut formaté
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Brouillon',
            'sent' => 'Envoyée',
            'paid' => 'Payée',
            'overdue' => 'En retard',
            'cancelled' => 'Annulée',
            default => 'Inconnu'
        };
    }
}