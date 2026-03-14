<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de connexion réussie
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'etudiant',
            'status' => 'approved'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role'
                ]
            ]
        ]);
    }

    /**
     * Test de connexion échouée avec mauvais mot de passe
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Identifiants incorrects'
        ]);
    }

    /**
     * Test de connexion échouée avec email inexistant
     */
    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Identifiants incorrects'
        ]);
    }

    /**
     * Test d'inscription réussie
     */
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/create', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'role',
                'status'
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'role' => 'etudiant', // Étudiant par défaut
            'status' => 'pending'
        ]);
    }

    /**
     * Test de validation lors de l'inscription
     */
    public function test_registration_validation(): void
    {
        $response = $this->postJson('/api/create', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name',
            'email',
            'password'
        ]);
    }

    /**
     * Test qu'un utilisateur non approuvé ne peut pas se connecter
     */
    public function test_pending_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'pending@example.com',
            'password' => Hash::make('password123'),
            'status' => 'pending'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'pending@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Votre compte est en attente d\'approbation par l\'admin'
        ]);
    }

    /**
     * Test de déconnexion
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->approved()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Test de récupération du profil utilisateur
     */
    public function test_user_can_get_profile(): void
    {
        $user = User::factory()->approved()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'email',
                'role'
            ]
        ]);
        $response->assertJsonFragment([
            'id' => $user->id,
            'email' => $user->email
        ]);
    }

    /**
     * Test de l'API de test
     */
    public function test_api_test_endpoint(): void
    {
        $response = $this->getJson('/api/test');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'API OK'
        ]);
    }
}