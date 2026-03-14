<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Enrollement;
use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\AcademicYear;
use Laravel\Sanctum\Sanctum;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $student;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createTestData();
    }

    private function createTestData()
    {
        // Créer les rôles
        Role::create(['nom' => 'admin', 'libelle' => 'Administrateur']);
        Role::create(['nom' => 'gestion', 'libelle' => 'Gestionnaire']);
        Role::create(['nom' => 'etudiant', 'libelle' => 'Étudiant']);

        // Créer les utilisateurs
        $this->admin = User::factory()->create([
            'role_id' => 1,
            'status' => 'approved'
        ]);

        $this->student = User::factory()->create([
            'role_id' => 3,
            'status' => 'pending'
        ]);
    }

    /**
     * Test qu'un admin peut voir tous les utilisateurs
     */
    public function test_admin_can_view_all_users(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'status',
                    'role_id'
                ]
            ]
        ]);
    }

    /**
     * Test qu'un étudiant ne peut pas accéder aux routes admin
     */
    public function test_student_cannot_access_admin_routes(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    /**
     * Test qu'un admin peut approuver un utilisateur
     */
    public function test_admin_can_approve_user(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/admin/users/{$this->student->id}/approve");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Utilisateur approuvé avec succès'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->student->id,
            'status' => 'approved'
        ]);
    }

    /**
     * Test qu'un admin peut rejeter un utilisateur
     */
    public function test_admin_can_reject_user(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/admin/users/{$this->student->id}/reject", [
            'rejection_reason' => 'Documents manquants'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Utilisateur rejeté'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->student->id,
            'status' => 'rejected'
        ]);
    }

    /**
     * Test qu'un admin peut voir tous les enrollements
     */
    public function test_admin_can_view_all_enrollments(): void
    {
        // Créer des données de test
        $departement = Departement::create([
            'nom' => 'Informatique',
            'code' => 'INFO',
            'description' => 'Département Informatique'
        ]);

        $filiere = Filiere::create([
            'nom' => 'Génie Logiciel',
            'code' => 'GL',
            'departement_id' => $departement->id,
            'description' => 'Formation en développement logiciel'
        ]);

        $niveau = Niveau::create([
            'nom' => 'Licence 1',
            'code' => 'L1',
            'filiere_id' => $filiere->id,
            'frais_inscription' => 150000
        ]);

        $academicYear = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_active' => true
        ]);

        $enrollment = Enrollement::create([
            'user_id' => $this->student->id,
            'filiere_id' => $filiere->id,
            'niveau_id' => $niveau->id,
            'academic_year_id' => $academicYear->id,
            'nom' => 'Doe',
            'prenom' => 'John',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'telephone' => '237123456789',
            'adresse' => '123 Rue Test',
            'status' => 'pending'
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/enrollements');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $enrollment->id,
            'status' => 'pending'
        ]);
    }

    /**
     * Test qu'un admin peut créer un département
     */
    public function test_admin_can_create_departement(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/admin/departements', [
            'nom' => 'Mathématiques',
            'code' => 'MATH',
            'description' => 'Département de Mathématiques'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('departements', [
            'nom' => 'Mathématiques',
            'code' => 'MATH'
        ]);
    }

    /**
     * Test qu'un admin peut créer une filière
     */
    public function test_admin_can_create_filiere(): void
    {
        $departement = Departement::create([
            'nom' => 'Informatique',
            'code' => 'INFO',
            'description' => 'Département Informatique'
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/admin/filieres', [
            'nom' => 'Intelligence Artificielle',
            'code' => 'IA',
            'departement_id' => $departement->id,
            'description' => 'Formation en IA'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('filieres', [
            'nom' => 'Intelligence Artificielle',
            'code' => 'IA',
            'departement_id' => $departement->id
        ]);
    }

    /**
     * Test qu'un admin peut créer un niveau
     */
    public function test_admin_can_create_niveau(): void
    {
        $departement = Departement::create([
            'nom' => 'Informatique',
            'code' => 'INFO',
            'description' => 'Département Informatique'
        ]);

        $filiere = Filiere::create([
            'nom' => 'Génie Logiciel',
            'code' => 'GL',
            'departement_id' => $departement->id,
            'description' => 'Formation en développement logiciel'
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/admin/niveaux', [
            'nom' => 'Master 1',
            'code' => 'M1',
            'filiere_id' => $filiere->id,
            'frais_inscription' => 200000
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('niveaux', [
            'nom' => 'Master 1',
            'code' => 'M1',
            'filiere_id' => $filiere->id,
            'frais_inscription' => 200000
        ]);
    }

    /**
     * Test qu'un admin peut supprimer un utilisateur
     */
    public function test_admin_can_delete_user(): void
    {
        $userToDelete = User::factory()->create([
            'role_id' => 3,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/admin/users/{$userToDelete->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Utilisateur supprimé avec succès'
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $userToDelete->id
        ]);
    }

    /**
     * Test qu'un admin peut voir les statistiques du dashboard
     */
    public function test_admin_can_view_dashboard_stats(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/dashboard/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_users',
            'pending_users',
            'approved_users',
            'total_enrollments',
            'pending_enrollments',
            'approved_enrollments'
        ]);
    }

    /**
     * Test qu'un admin peut générer des rapports
     */
    public function test_admin_can_generate_reports(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/reports/global-stats');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test de validation lors de la création d'un département
     */
    public function test_departement_creation_validation(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/admin/departements', [
            'nom' => '',
            'code' => '',
            'description' => ''
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'nom',
            'code'
        ]);
    }

    /**
     * Test qu'un admin ne peut pas supprimer son propre compte
     */
    public function test_admin_cannot_delete_own_account(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/admin/users/{$this->admin->id}");

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Vous ne pouvez pas supprimer votre propre compte'
        ]);
    }
}