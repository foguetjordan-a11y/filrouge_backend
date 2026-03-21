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
        'nom',
        'code',
        'filiere_id',
        'frais_inscription',
        'description',
        'ordre',
    ];

    // Sync libelle <-> nom
    public function getNomAttribute()
    {
        return $this->attributes['nom'] ?? $this->attributes['libelle'] ?? null;
    }

    public function setNomAttribute($value)
    {
        $this->attributes['nom'] = $value;
        $this->attributes['libelle'] = $value;
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function enrollements()
    {
        return $this->hasMany(Enrollement::class, 'niveau_id');
    }
}
