<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Niveau extends Model
{
    use HasFactory;

    protected $table = 'niveaux';

    protected $fillable = [
        'libelle',
        'description',
        'ordre'
    ];

    /**
     * Accessor pour compatibilité avec 'nom'
     */
    public function getNomAttribute()
    {
        return $this->libelle;
    }

    /**
     * Mutator pour compatibilité avec 'nom'
     */
    public function setNomAttribute($value)
    {
        $this->attributes['libelle'] = $value;
    }

    /**
     * Relation avec les enrôlements
     */
    public function enrollements()
    {
        return $this->hasMany(Enrollement::class, 'niveau_id');
    }

    /**
     * Relation avec les filières (si applicable)
     */
    public function filieres()
    {
        return $this->belongsToMany(Filiere::class, 'filiere_niveau', 'niveau_id', 'filiere_id');
    }
}
