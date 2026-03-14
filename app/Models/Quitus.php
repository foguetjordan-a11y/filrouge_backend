<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quitus extends Model
{
    use HasFactory;

    protected $table = 'quitus'; // Spécifier explicitement le nom de la table

    protected $fillable = [
        'user_id',
        'enrollement_id',
        'annee_academique_id',
        'reference',
        'date_emission',
        'statut',
        'pdf_path',
    ];

    protected $casts = [
        'date_emission' => 'date',
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
     * Relation avec l'année académique
     */
    public function anneeAcademique()
    {
        return $this->belongsTo(AcademicYear::class, 'annee_academique_id');
    }
}
