<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Enrollement;
use App\Models\User;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\AcademicYear;
use App\Models\Departement;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnrollementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createTestData();
    }

    private function createTestData()
    {
        // Créer un département
        $this->departement = Departement::create([
            'nom' => 'Informatique',
            'code' => 'INFO',
            'description' => 'Département Informatique'
        ]);

        // Créer une filière
        $this->filiere = Filiere::create([
            'nom' => 'Génie Logiciel',
            'code' => 'GL',
            'departement_id' => $this->departement->id,
            'description' => 'Formation en développement logiciel'
        ]);

        // Créer un niveau
        $this->niveau = Niveau::create([
            'nom' => 'Licence 1',
            'code' => 'L1',
            'filiere_id' => $this->filiere->id,
            'frais_inscription' => 150000
        ]);

        // Créer une année académique
        $this->academicYear = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_active' => true
        ]);

        // Créer un utilisateur
        $this->user = User::factory()->create([
            'role_id' => 3,
            'status' => 'approved'
        ]);
    }

    /**
     * Test de création d'un enrollement
     */
    public function test_enrollement_creation(): void
    {
        $enrollement = Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Doe',
            'prenom' => 'John',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'telephone' => '237123456789',
            'adresse' => '123 Rue Test',
            'status' => 'pending'
        ]);

        $this->assertInstanceOf(Enrollement::class, $enrollement);
        $this->assertEquals('Doe', $enrollement->nom);
        $this->assertEquals('John', $enrollement->prenom);
        $this->assertEquals('pending', $enrollement->status);
    }

    /**
     * Test des relations de l'enrollement
     */
    public function test_enrollement_relationships(): void
    {
        $enrollement = Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Doe',
            'prenom' => 'John',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'telephone' => '237123456789',
            'adresse' => '123 Rue Test'
        ]);

        // Test relation avec User
        $this->assertInstanceOf(User::class, $enrollement->user);
        $this->assertEquals($this->user->id, $enrollement->user->id);

        // Test relation avec Filiere
        $this->assertInstanceOf(Filiere::class, $enrollement->filiere);
        $this->assertEquals($this->filiere->id, $enrollement->filiere->id);

        // Test relation avec Niveau
        $this->assertInstanceOf(Niveau::class, $enrollement->niveau);
        $this->assertEquals($this->niveau->id, $enrollement->niveau->id);

        // Test relation avec AcademicYear
        $this->assertInstanceOf(AcademicYear::class, $enrollement->academicYear);
        $this->assertEquals($this->academicYear->id, $enrollement->academicYear->id);
    }

    /**
     * Test des méthodes d'état de l'enrollement
     */
    public function test_enrollement_status_methods(): void
    {
        $pendingEnrollment = Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Doe',
            'prenom' => 'John',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'telephone' => '237123456789',
            'adresse' => '123 Rue Test',
            'status' => 'pending'
        ]);

        $approvedEnrollment = Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Smith',
            'prenom' => 'Jane',
            'date_naissance' => '2001-01-01',
            'lieu_naissance' => 'Douala',
            'telephone' => '237987654321',
            'adresse' => '456 Rue Test',
            'status' => 'approved'
        ]);

        $this->assertTrue($pendingEnrollment->isPending());
        $this->assertFalse($pendingEnrollment->isApproved());
        $this->assertFalse($pendingEnrollment->isRejected());

        $this->assertTrue($approvedEnrollment->isApproved());
        $this->assertFalse($approvedEnrollment->isPending());
        $this->assertFalse($approvedEnrollment->isRejected());
    }

    /**
     * Test de la méthode getFullNameAttribute
     */
    public function test_full_name_attribute(): void
    {
        $enrollement = Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Doe',
            'prenom' => 'John',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'telephone' => '237123456789',
            'adresse' => '123 Rue Test'
        ]);

        $this->assertEquals('John Doe', $enrollement->full_name);
    }

    /**
     * Test de la méthode canBeApproved
     */
    public function test_can_be_approved(): void
    {
        $pendingEnrollment = Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Doe',
            'prenom' => 'John',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'telephone' => '237123456789',
            'adresse' => '123 Rue Test',
            'status' => 'pending'
        ]);

        $approvedEnrollment = Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Smith',
            'prenom' => 'Jane',
            'date_naissance' => '2001-01-01',
            'lieu_naissance' => 'Douala',
            'telephone' => '237987654321',
            'adresse' => '456 Rue Test',
            'status' => 'approved'
        ]);

        $this->assertTrue($pendingEnrollment->canBeApproved());
        $this->assertFalse($approvedEnrollment->canBeApproved());
    }

    /**
     * Test de la méthode hasPayment
     */
    public function test_has_payment(): void
    {
        $enrollmentWithoutPayment = Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Doe',
            'prenom' => 'John',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'telephone' => '237123456789',
            'adresse' => '123 Rue Test',
            'payment_status' => 'pending'
        ]);

        $enrollmentWithPayment = Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Smith',
            'prenom' => 'Jane',
            'date_naissance' => '2001-01-01',
            'lieu_naissance' => 'Douala',
            'telephone' => '237987654321',
            'adresse' => '456 Rue Test',
            'payment_status' => 'paid'
        ]);

        $this->assertFalse($enrollmentWithoutPayment->hasPayment());
        $this->assertTrue($enrollmentWithPayment->hasPayment());
    }

    /**
     * Test de la contrainte d'unicité
     */
    public function test_unique_constraint(): void
    {
        // Créer le premier enrollement
        Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Doe',
            'prenom' => 'John',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'telephone' => '237123456789',
            'adresse' => '123 Rue Test'
        ]);

        // Tenter de créer un doublon (doit échouer)
        $this->expectException(\Illuminate\Database\QueryException::class);

        Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Doe',
            'prenom' => 'John',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'telephone' => '237123456789',
            'adresse' => '123 Rue Test'
        ]);
    }

    /**
     * Test des scopes
     */
    public function test_scopes(): void
    {
        // Créer des enrollements avec différents statuts
        $pending = Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Pending',
            'prenom' => 'User',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'telephone' => '237123456789',
            'adresse' => '123 Rue Test',
            'status' => 'pending'
        ]);

        $approved = Enrollement::create([
            'user_id' => $this->user->id,
            'filiere_id' => $this->filiere->id,
            'niveau_id' => $this->niveau->id,
            'academic_year_id' => $this->academicYear->id,
            'nom' => 'Approved',
            'prenom' => 'User',
            'date_naissance' => '2001-01-01',
            'lieu_naissance' => 'Douala',
            'telephone' => '237987654321',
            'adresse' => '456 Rue Test',
            'status' => 'approved'
        ]);

        // Test du scope pending
        $pendingEnrollments = Enrollement::pending()->get();
        $this->assertCount(1, $pendingEnrollments);
        $this->assertEquals($pending->id, $pendingEnrollments->first()->id);

        // Test du scope approved
        $approvedEnrollments = Enrollement::approved()->get();
        $this->assertCount(1, $approvedEnrollments);
        $this->assertEquals($approved->id, $approvedEnrollments->first()->id);
    }
}