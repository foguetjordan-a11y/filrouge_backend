<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filiere_id',
        'niveau_id',
        'annee_academique_id',
        'academic_year_id',
        // Infos personnelles
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'telephone',
        'adresse',
        // Statuts
        'statut',
        'status',
        'payment_status',
        'payment_amount',
        'payment_due_date',
        'paid_at',
        // Autres
        'photo',
        'matricule_etudiant',
        'type_inscription',
        'validated_at',
        'validated_by',
        'motif_rejet',
        'rejected_at',
    ];

    protected $casts = [
        'date_naissance'   => 'date',
        'payment_amount'   => 'decimal:2',
        'payment_due_date' => 'datetime',
        'paid_at'          => 'datetime',
        'validated_at'     => 'datetime',
        'rejected_at'      => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    /** Relation via academic_year_id (utilisé dans les tests) */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /** Relation via annee_academique_id (ancien nom) */
    public function anneeAcademique()
    {
        return $this->belongsTo(AcademicYear::class, 'annee_academique_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // ── Accesseurs ───────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }

    // ── Méthodes de statut (utilisées dans les tests) ────────────

    public function isPending(): bool
    {
        return $this->status === 'pending' || $this->statut === 'en_attente';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved' || $this->statut === 'valide';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected' || $this->statut === 'rejete';
    }

    public function canBeApproved(): bool
    {
        return $this->isPending();
    }

    public function hasPayment(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function markAsPaid(): void
    {
        $this->update(['payment_status' => 'paid', 'paid_at' => now()]);
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Anciens scopes (compatibilité)
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopeRejete($query)
    {
        return $query->where('statut', 'rejete');
    }
}
