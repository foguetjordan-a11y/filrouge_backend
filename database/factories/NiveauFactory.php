<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Niveau;
use App\Models\Filiere;

class NiveauFactory extends Factory
{
    protected $model = Niveau::class;

    public function definition(): array
    {
        $niveaux = [
            ['nom' => 'Licence 1', 'code' => 'L1', 'frais' => 150000],
            ['nom' => 'Licence 2', 'code' => 'L2', 'frais' => 160000],
            ['nom' => 'Licence 3', 'code' => 'L3', 'frais' => 170000],
            ['nom' => 'Master 1', 'code' => 'M1', 'frais' => 200000],
            ['nom' => 'Master 2', 'code' => 'M2', 'frais' => 220000],
        ];

        $niveau = $this->faker->randomElement($niveaux);

        return [
            'nom' => $niveau['nom'],
            'code' => $niveau['code'],
            'filiere_id' => Filiere::factory(),
            'frais_inscription' => $niveau['frais'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}