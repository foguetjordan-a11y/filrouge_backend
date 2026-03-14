<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'description', 'chef_departement'];

    public function filieres()
    {
        return $this->hasMany(Filiere::class);
    }
}
