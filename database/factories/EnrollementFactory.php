<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Enrollement;
use App\Models\User;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\AcademicYear;

class EnrollementFactory extends Factory
{
    protected $model = Enrollement::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'filiere_id' => Filiere::factory(),
            'niveau_id' => Niveau::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'date_naissance' => $this->faker->date('Y-m-d', '2005-12-31'),
            'lieu_naissance' => $this->faker->city(),
            'telephone' => '237' . $this->faker->numerify('########'),
            'adresse' => $this->faker->address(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
        ]);
    }
}