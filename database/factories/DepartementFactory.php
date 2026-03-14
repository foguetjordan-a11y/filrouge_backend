<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Departement;

class DepartementFactory extends Factory
{
    protected $model = Departement::class;

    public function definition(): array
    {
        $departements = [
            ['nom' => 'Informatique', 'code' => 'INFO'],
            ['nom' => 'Mathématiques', 'code' => 'MATH'],
            ['nom' => 'Physique', 'code' => 'PHYS'],
            ['nom' => 'Chimie', 'code' => 'CHIM'],
            ['nom' => 'Biologie', 'code' => 'BIO'],
        ];

        $departement = $this->faker->randomElement($departements);

        return [
            'nom' => $departement['nom'],
            'code' => $departement['code'],
            'description' => $this->faker->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}