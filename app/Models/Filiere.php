<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'departement_id', 'description', 'duree_etudes', 'diplome_delivre'];

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }
    public function enrollements()
    {
    return $this->hasMany(Enrollement::class);
    }
}