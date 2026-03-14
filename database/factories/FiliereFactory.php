<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Filiere;
use App\Models\Departement;

class FiliereFactory extends Factory
{
    protected $model = Filiere::class;

    public function definition(): array
    {
        $filieres = [
            ['nom' => 'Génie Logiciel', 'code' => 'GL'],
            ['nom' => 'Réseaux et Télécommunications', 'code' => 'RT'],
            ['nom' => 'Intelligence Artificielle', 'code' => 'IA'],
            ['nom' => 'Cybersécurité', 'code' => 'CS'],
            ['nom' => 'Systèmes d\'Information', 'code' => 'SI'],
        ];

        $filiere = $this->faker->randomElement($filieres);

        return [
            'nom' => $filiere['nom'],
            'code' => $filiere['code'],
            'departement_id' => Departement::factory(),
            'description' => $this->faker->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}