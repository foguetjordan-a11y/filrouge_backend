<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de l'endpoint de test de l'API
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

    /**
     * Test d'accès aux routes protégées sans authentification
     */
    public function test_protected_routes_require_authentication(): void
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);

        $response = $this->getJson('/api/enrollements');
        $response->assertStatus(401);
    }

    /**
     * Test d'accès aux routes admin sans permissions
     */
    public function test_admin_routes_require_admin_role(): void
    {
        $student = User::factory()->create([
            'role' => 'etudiant',
            'status' => 'approved'
        ]);

        Sanctum::actingAs($student);

        $response = $this->getJson('/api/admin/enrollements');
        $response->assertStatus(403);

        $response = $this->getJson('/api/users');
        $response->assertStatus(403);
    }

    /**
     * Test d'accès aux routes étudiant avec le bon rôle
     */
    public function test_student_can_access_student_routes(): void
    {
        $student = User::factory()->create([
            'role' => 'etudiant',
            'status' => 'approved'
        ]);

        Sanctum::actingAs($student);

        $response = $this->getJson('/api/enrollements');
        $response->assertStatus(200);

        $response = $this->getJson('/api/me');
        $response->assertStatus(200);
    }

    /**
     * Test d'accès aux routes admin avec le bon rôle
     */
    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'approved'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/enrollements');
        $response->assertStatus(200);

        $response = $this->getJson('/api/users');
        $response->assertStatus(200);
    }
}