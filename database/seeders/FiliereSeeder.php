<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Filiere;
use App\Models\Departement;

class FiliereSeeder extends Seeder
{
    public function run()
    {
        $departement = Departement::first();

    Filiere::create([
        'nom' => 'Informatique',
        'departement_id' => $departement->id
    ]);

    Filiere::create([
        'nom' => 'Génie Logiciel',
        'departement_id' => $departement->id
    ]);
    }
}
