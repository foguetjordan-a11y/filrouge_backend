<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de création d'un utilisateur
     */
    public function test_user_creation(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'etudiant'
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('etudiant', $user->role);
    }

    /**
     * Test des méthodes d'état de l'utilisateur
     */
    public function test_user_status_methods(): void
    {
        $pendingUser = User::factory()->create(['status' => 'pending']);
        $approvedUser = User::factory()->create(['status' => 'approved']);
        $rejectedUser = User::factory()->create(['status' => 'rejected']);

        $this->assertEquals('pending', $pendingUser->status);
        $this->assertEquals('approved', $approvedUser->status);
        $this->assertEquals('rejected', $rejectedUser->status);
    }

    /**
     * Test de la méthode getFullNameAttribute
     */
    public function test_full_name_attribute(): void
    {
        $user = User::factory()->create([
            'nom' => 'Doe',
            'prenom' => 'John'
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    /**
     * Test de la factory avec différents états
     */
    public function test_user_factory_states(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assertEquals('admin', $admin->role);
        $this->assertEquals('approved', $admin->status);

        $student = User::factory()->etudiant()->create();
        $this->assertEquals('etudiant', $student->role);

        $approved = User::factory()->approved()->create();
        $this->assertEquals('approved', $approved->status);
    }
}