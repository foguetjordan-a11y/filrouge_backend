<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'etudiant',
            'role_id' => 3,
            'status' => 'pending',
            'complete_profile' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'role_id' => 1,
            'status' => 'approved',
        ]);
    }

    public function gestion(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'gestion',
            'role_id' => 2,
            'status' => 'approved',
        ]);
    }

    public function etudiant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'etudiant',
            'role_id' => 3,
        ]);
    }

    /**
     * Create an approved user.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Create a rejected user.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * Create a user with complete profile.
     */
    public function withCompleteProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'sexe' => fake()->randomElement(['M', 'F']),
            'date_naissance' => fake()->date('Y-m-d', '2005-12-31'),
            'lieu_naissance' => fake()->city(),
            'nationalite' => 'Camerounaise',
            'adresse' => fake()->address(),
            'telephone' => '237' . fake()->numerify('########'),
            'is_profile_complete' => true,
            'profile_completed_at' => now(),
        ]);
    }
}
