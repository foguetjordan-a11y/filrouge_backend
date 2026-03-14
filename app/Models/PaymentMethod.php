<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'configuration',
        'is_active',
        'min_amount',
        'max_amount',
        'fee_percentage',
        'fee_fixed',
        'sort_order'
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'fee_percentage' => 'decimal:2',
        'fee_fixed' => 'decimal:2'
    ];

    /**
     * Relation avec les paiements
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope pour les méthodes actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour ordonner par ordre d'affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Calculer les frais pour un montant donné
     */
    public function calculateFees(float $amount): float
    {
        $percentageFee = ($amount * $this->fee_percentage) / 100;
        return $percentageFee + $this->fee_fixed;
    }

    /**
     * Vérifier si le montant est dans les limites
     */
    public function isAmountValid(float $amount): bool
    {
        if ($amount < $this->min_amount) {
            return false;
        }

        if ($this->max_amount && $amount > $this->max_amount) {
            return false;
        }

        return true;
    }

    /**
     * Obtenir la configuration pour un paramètre spécifique
     */
    public function getConfig(string $key, $default = null)
    {
        return $this->configuration[$key] ?? $default;
    }
}