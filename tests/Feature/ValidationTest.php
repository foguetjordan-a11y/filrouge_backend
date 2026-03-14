<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de validation lors de la création d'utilisateur
     */
    public function test_user_creation_validation(): void
    {
        // Test avec des données manquantes
        $response = $this->postJson('/api/create', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test de validation d'email unique
     */
    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/create', [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test de validation du mot de passe
     */
    public function test_password_validation(): void
    {
        $response = $this->postJson('/api/create', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123', // Trop court
            'password_confirmation' => '456' // Ne correspond pas
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }
}