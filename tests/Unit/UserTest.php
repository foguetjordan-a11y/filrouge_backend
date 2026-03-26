<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Enrollement;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::create(['nom' => 'admin',    'libelle' => 'Administrateur', 'name' => 'admin',    'guard_name' => 'web']);
        Role::create(['nom' => 'gestion',  'libelle' => 'Gestionnaire',   'name' => 'gestion',  'guard_name' => 'web']);
        Role::create(['nom' => 'etudiant', 'libelle' => 'Étudiant',       'name' => 'etudiant', 'guard_name' => 'web']);
    }

    /**
     * Test de création d'un utilisateur
     */
    public function test_user_creation(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role_id' => 3
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals(3, $user->role_id);
    }

    /**
     * Test de la relation avec le rôle
     */
    public function test_user_role_relationship(): void
    {
        $user = User::factory()->create([
            'role_id' => 3
        ]);

        $roleModel = $user->role()->first();
        $this->assertInstanceOf(Role::class, $roleModel);
        $this->assertEquals('etudiant', $roleModel->nom);
    }

    /**
     * Test de la relation avec les enrollements
     */
    public function test_user_enrollements_relationship(): void
    {
        $user = User::factory()->create([
            'role_id' => 3
        ]);

        // Créer des enrollements pour cet utilisateur
        $enrollements = Enrollement::factory()->count(2)->create([
            'user_id' => $user->id
        ]);

        $this->assertCount(2, $user->enrollements);
        $this->assertInstanceOf(Enrollement::class, $user->enrollements->first());
    }

    /**
     * Test des méthodes d'état de l'utilisateur
     */
    public function test_user_status_methods(): void
    {
        $pendingUser = User::factory()->create(['status' => 'pending']);
        $approvedUser = User::factory()->create(['status' => 'approved']);
        $rejectedUser = User::factory()->create(['status' => 'rejected']);

        $this->assertTrue($pendingUser->isPending());
        $this->assertFalse($pendingUser->isApproved());
        $this->assertFalse($pendingUser->isRejected());

        $this->assertTrue($approvedUser->isApproved());
        $this->assertFalse($approvedUser->isPending());
        $this->assertFalse($approvedUser->isRejected());

        $this->assertTrue($rejectedUser->isRejected());
        $this->assertFalse($rejectedUser->isPending());
        $this->assertFalse($rejectedUser->isApproved());
    }

    /**
     * Test des méthodes de rôle
     */
    public function test_user_role_methods(): void
    {
        $admin    = User::factory()->create(['role' => 'admin',    'role_id' => 1]);
        $gestion  = User::factory()->create(['role' => 'gestion',  'role_id' => 2]);
        $etudiant = User::factory()->create(['role' => 'etudiant', 'role_id' => 3]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isGestion());
        $this->assertFalse($admin->isEtudiant());

        $this->assertTrue($gestion->isGestion());
        $this->assertFalse($gestion->isAdmin());
        $this->assertFalse($gestion->isEtudiant());

        $this->assertTrue($etudiant->isEtudiant());
        $this->assertFalse($etudiant->isAdmin());
        $this->assertFalse($etudiant->isGestion());
    }

    /**
     * Test de la génération du matricule
     */
    public function test_matricule_generation(): void
    {
        $user = User::factory()->create([
            'role_id' => 3,
            'status' => 'approved'
        ]);

        // Simuler la génération du matricule
        $matricule = $user->generateMatricule();

        $this->assertNotNull($matricule);
        $this->assertStringStartsWith('ETU', $matricule);
        $this->assertEquals(11, strlen($matricule)); // ETU + 8 chiffres
    }

    /**
     * Test de validation des données utilisateur
     */
    public function test_user_validation(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Tenter de créer un utilisateur sans email (requis et unique)
        User::create([
            'name' => 'Test User',
            'password' => bcrypt('password'),
            'role_id' => 3
        ]);
    }

    /**
     * Test de la méthode getFullNameAttribute
     */
    public function test_full_name_attribute(): void
    {
        $user = User::factory()->create([
            'nom'    => 'Doe',
            'prenom' => 'John',
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    /**
     * Test de la méthode hasCompleteProfile
     */
    public function test_has_complete_profile(): void
    {
        $incompleteUser = User::factory()->create([
            'complete_profile' => false
        ]);

        $completeUser = User::factory()->create([
            'complete_profile' => true
        ]);

        $this->assertFalse($incompleteUser->hasCompleteProfile());
        $this->assertTrue($completeUser->hasCompleteProfile());
    }
}
