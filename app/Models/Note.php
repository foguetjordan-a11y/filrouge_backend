<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'enrollement_id',
        'matiere_id',
        'note'
    ];

    public function enrollement()
    {
        return $this->belongsTo(Enrollement::class);
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }
}

