<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollement extends Model
{
     protected $fillable = [
        'user_id',
        'filiere_id',
        'niveau_id',
        'annee_academique_id',
        'date_enrollement',
        'statut',
        'photo',
        'payment_status',
        'payment_amount',
        'payment_due_date',
        'paid_at',
        'academic_year',
        // Champs d'enrôlement essentiels
        'matricule_etudiant',
        'type_inscription',
        'validated_at',
        'validated_by',
        'motif_rejet',
        'rejected_at'
    ];

    protected $casts = [
        'date_enrollement' => 'date',
        'payment_amount' => 'decimal:2',
        'payment_due_date' => 'datetime',
        'paid_at' => 'datetime',
        'validated_at' => 'datetime',
        'rejected_at' => 'datetime'
    ];

    public function etudiant()
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

    public function anneeAcademique()
    {
        return $this->belongsTo(AcademicYear::class, 'annee_academique_id');
    }

    // Relation avec l'utilisateur (alias pour compatibilité)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relations avec le système de paiement
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function successfulPayment()
    {
        return $this->hasOne(Payment::class)->where('status', 'completed');
    }

    public function activeInvoice()
    {
        return $this->hasOne(Invoice::class)->whereIn('status', ['sent', 'overdue']);
    }

    // Scopes pour les statuts de paiement
    public function scopePaymentPending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaymentPaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePaymentRequired($query)
    {
        return $query->whereIn('payment_status', ['pending', 'failed']);
    }

    // Méthodes utilitaires pour le paiement
    public function requiresPayment(): bool
    {
        return in_array($this->payment_status, ['pending', 'failed']);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPaymentOverdue(): bool
    {
        return $this->payment_status === 'pending' && 
               $this->payment_due_date && 
               $this->payment_due_date < now();
    }

    public function markAsPaymentRequired(float $amount, $dueDate = null)
    {
        $this->update([
            'payment_status' => 'pending',
            'payment_amount' => $amount,
            'payment_due_date' => $dueDate ?? now()->addDays(30)
        ]);
    }

    public function markAsPaid()
    {
        $this->update([
            'payment_status' => 'paid',
            'paid_at' => now()
        ]);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'not_required' => 'Paiement non requis',
            'pending' => 'Paiement en attente',
            'paid' => 'Payé',
            'failed' => 'Paiement échoué',
            default => 'Statut inconnu'
        };
    }

    // Relation avec l'admin qui a validé
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Vérifier si l'enrôlement est complet (profil utilisateur requis)
    public function isEnrollmentComplete(): bool
    {
        return $this->user && $this->user->isProfileComplete();
    }

    // Méthodes pour la validation
    public function markAsValidated(User $admin): void
    {
        $this->update([
            'statut' => 'valide',
            'validated_at' => now(),
            'validated_by' => $admin->id,
            'motif_rejet' => null,
            'rejected_at' => null
        ]);
    }

    public function markAsRejected(User $admin, string $motif = null): void
    {
        $this->update([
            'statut' => 'rejete',
            'validated_at' => null,
            'validated_by' => $admin->id,
            'motif_rejet' => $motif,
            'rejected_at' => now()
        ]);
    }

    // Vérifier si l'étudiant peut recevoir un matricule
    public function canGenerateMatricule(): bool
    {
        return $this->statut === 'valide' && 
               $this->isPaid() && 
               empty($this->matricule_etudiant) &&
               $this->user->canGenerateMatricule();
    }

    // Scopes pour les statuts d'enrôlement
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

    public function scopeWithMatricule($query)
    {
        return $query->whereNotNull('matricule_etudiant');
    }

    public function scopeWithoutMatricule($query)
    {
        return $query->whereNull('matricule_etudiant');
    }

    public function scopeProfileComplete($query)
    {
        return $query->whereHas('user', function($q) {
            $q->where('is_profile_complete', true);
        });
    }

    // Accesseurs
    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'en_attente' => 'En attente de validation',
            'valide' => 'Validé',
            'rejete' => 'Rejeté',
            default => 'Statut inconnu'
        };
    }

    public function getTypeInscriptionLabelAttribute(): string
    {
        return match($this->type_inscription) {
            'nouvelle' => 'Nouvelle inscription',
            'reinscription' => 'Réinscription',
            default => 'Type inconnu'
        };
    }
}
