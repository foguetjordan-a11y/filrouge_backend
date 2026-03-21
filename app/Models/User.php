<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'status',
        'complete_profile',
        // Informations personnelles complètes
        'nom',
        'prenom',
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'nationalite',
        'adresse',
        'telephone',
        'photo_identite',
        'numero_cni',
        'numero_passeport',
        // Informations académiques
        'type_inscription',
        'profile_completed_at',
        'is_profile_complete'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_naissance' => 'date',
        'profile_completed_at' => 'datetime',
        'is_profile_complete' => 'boolean'
    ];

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }
    
    public function enrollements()
    {
        return $this->hasMany(Enrollement::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function quitus()
    {
        return $this->hasOne(Quitus::class);
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

    // ── Méthodes de statut ───────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    // ── Méthodes de rôle ─────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role_id === 1 || $this->role === 'admin';
    }

    public function isGestion(): bool
    {
        return $this->role_id === 2 || $this->role === 'gestion';
    }

    public function isEtudiant(): bool
    {
        return $this->role_id === 3 || $this->role === 'etudiant';
    }

    // ── Profil ───────────────────────────────────────────────────

    public function hasCompleteProfile(): bool
    {
        return (bool) ($this->complete_profile ?? $this->is_profile_complete ?? false);
    }

    // Méthodes utilitaires pour le profil
    public function getFullNameAttribute(): string
    {
        if ($this->prenom && $this->nom) {
            return $this->prenom . ' ' . $this->nom;
        }
        return $this->name ?? 'Utilisateur';
    }

    public function generateMatricule(): string
    {
        return 'ETU' . str_pad($this->id, 8, '0', STR_PAD_LEFT);
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->date_naissance) {
            return null;
        }
        return $this->date_naissance->age;
    }

    public function isProfileComplete(): bool
    {
        $requiredFields = [
            'nom', 'prenom', 'sexe', 'date_naissance', 
            'lieu_naissance', 'telephone', 'adresse'
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }

        return true;
    }

    public function markProfileAsComplete(): void
    {
        if ($this->isProfileComplete()) {
            $this->update([
                'is_profile_complete' => true,
                'profile_completed_at' => now()
            ]);
        }
    }

    public function hasMatricule(): bool
    {
        return $this->enrollements()
            ->where('statut', 'valide')
            ->whereNotNull('matricule_etudiant')
            ->exists();
    }

    public function canGenerateMatricule(): bool
    {
        return $this->role === 'etudiant' && 
               $this->isProfileComplete() && 
               !$this->hasMatricule() &&
               $this->hasValidPaidEnrollment();
    }

    public function hasValidPaidEnrollment(): bool
    {
        return $this->enrollements()
            ->where('statut', 'valide')
            ->where('payment_status', 'paid')
            ->exists();
    }

    public function getMatricule(): ?string
    {
        $enrollment = $this->enrollements()
            ->where('statut', 'valide')
            ->whereNotNull('matricule_etudiant')
            ->orderBy('created_at', 'desc')
            ->first();
            
        return $enrollment ? $enrollment->matricule_etudiant : null;
    }

    // Scopes
    public function scopeEtudiants($query)
    {
        return $query->where('role', 'etudiant');
    }

    public function scopeWithMatricule($query)
    {
        return $query->whereHas('enrollements', function($q) {
            $q->where('statut', 'valide')
              ->whereNotNull('matricule_etudiant');
        });
    }

    public function scopeWithoutMatricule($query)
    {
        return $query->where('role', 'etudiant')
                     ->where(function($q) {
                         $q->whereDoesntHave('enrollements', function($subQ) {
                             $subQ->where('statut', 'valide')
                                  ->whereNotNull('matricule_etudiant');
                         })
                         ->orWhereHas('enrollements', function($subQ) {
                             $subQ->where('statut', 'valide')
                                  ->where('payment_status', 'paid')
                                  ->whereNull('matricule_etudiant');
                         });
                     });
    }

    public function scopeProfileComplete($query)
    {
        return $query->where('is_profile_complete', true);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
