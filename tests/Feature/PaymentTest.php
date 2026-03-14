<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Enrollement;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Departement;
use App\Models\AcademicYear;
use Laravel\Sanctum\Sanctum;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $student;
    protected $admin;
    protected $enrollment;
    protected $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createTestData();
    }

    private function createTestData()
    {
        // Créer un département
        $departement = Departement::create([
            'nom' => 'Informatique',
            'code' => 'INFO',
            'description' => 'Département Informatique'
        ]);

        // Créer une filière
        $filiere = Filiere::create([
            'nom' => 'Génie Logiciel',
            'code' => 'GL',
            'departement_id' => $departement->id,
            'description' => 'Formation en développement logiciel'
        ]);

        // Créer un niveau
        $niveau = Niveau::create([
            'nom' => 'Licence 1',
            'code' => 'L1',
            'filiere_id' => $filiere->id,
            'frais_inscription' => 150000
        ]);

        // Créer une année académique
        $academicYear = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_active' => true
        ]);

        // Créer les utilisateurs
        $this->student = User::factory()->create([
            'role_id' => 3,
            'status' => 'approved'
        ]);

        $this->admin = User::factory()->create([
            'role_id' => 1,
            'status' => 'approved'
        ]);

        // Créer un enrollment
        $this->enrollment = Enrollement::create([
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
            'status' => 'approved'
        ]);

        // Créer une méthode de paiement
        $this->paymentMethod = PaymentMethod::create([
            'name' => 'Mobile Money',
            'code' => 'MOMO',
            'is_active' => true,
            'description' => 'Paiement par Mobile Money'
        ]);
    }

    /**
     * Test de génération d'une facture
     */
    public function test_can_generate_invoice(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/enrollements/{$this->enrollment->id}/generate-invoice");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'invoice' => [
                'id',
                'invoice_number',
                'amount',
                'status'
            ]
        ]);

        $this->assertDatabaseHas('invoices', [
            'enrollement_id' => $this->enrollment->id,
            'status' => 'pending'
        ]);
    }

    /**
     * Test qu'on ne peut pas générer plusieurs factures pour le même enrollment
     */
    public function test_cannot_generate_multiple_invoices(): void
    {
        // Créer une facture existante
        Invoice::create([
            'enrollement_id' => $this->enrollment->id,
            'invoice_number' => 'INV-2024-001',
            'amount' => 150000,
            'currency' => 'XAF',
            'status' => 'pending'
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/enrollements/{$this->enrollment->id}/generate-invoice");

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Une facture existe déjà pour cet enrollment'
        ]);
    }

    /**
     * Test de création d'un paiement
     */
    public function test_can_create_payment(): void
    {
        // Créer une facture
        $invoice = Invoice::create([
            'enrollement_id' => $this->enrollment->id,
            'invoice_number' => 'INV-2024-001',
            'amount' => 150000,
            'currency' => 'XAF',
            'status' => 'pending'
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson('/api/payments', [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 150000,
            'reference' => 'MOMO123456789'
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'payment' => [
                'id',
                'amount',
                'status',
                'reference'
            ]
        ]);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 150000,
            'status' => 'pending'
        ]);
    }

    /**
     * Test qu'un admin peut confirmer un paiement
     */
    public function test_admin_can_confirm_payment(): void
    {
        // Créer une facture et un paiement
        $invoice = Invoice::create([
            'enrollement_id' => $this->enrollment->id,
            'invoice_number' => 'INV-2024-001',
            'amount' => 150000,
            'currency' => 'XAF',
            'status' => 'pending'
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 150000,
            'currency' => 'XAF',
            'reference' => 'MOMO123456789',
            'status' => 'pending'
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/payments/{$payment->id}/confirm");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Paiement confirmé avec succès'
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'confirmed'
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid'
        ]);

        $this->assertDatabaseHas('enrollements', [
            'id' => $this->enrollment->id,
            'payment_status' => 'paid'
        ]);
    }

    /**
     * Test qu'un admin peut rejeter un paiement
     */
    public function test_admin_can_reject_payment(): void
    {
        $invoice = Invoice::create([
            'enrollement_id' => $this->enrollment->id,
            'invoice_number' => 'INV-2024-001',
            'amount' => 150000,
            'currency' => 'XAF',
            'status' => 'pending'
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 150000,
            'currency' => 'XAF',
            'reference' => 'MOMO123456789',
            'status' => 'pending'
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/payments/{$payment->id}/reject", [
            'rejection_reason' => 'Référence invalide'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Paiement rejeté'
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'failed',
            'failure_reason' => 'Référence invalide'
        ]);
    }

    /**
     * Test de récupération de l'historique des paiements
     */
    public function test_student_can_view_payment_history(): void
    {
        $invoice = Invoice::create([
            'enrollement_id' => $this->enrollment->id,
            'invoice_number' => 'INV-2024-001',
            'amount' => 150000,
            'currency' => 'XAF',
            'status' => 'pending'
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 150000,
            'currency' => 'XAF',
            'reference' => 'MOMO123456789',
            'status' => 'confirmed'
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/payments/history');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $payment->id,
            'amount' => 150000,
            'status' => 'confirmed'
        ]);
    }

    /**
     * Test de téléchargement du reçu de paiement
     */
    public function test_can_download_payment_receipt(): void
    {
        $invoice = Invoice::create([
            'enrollement_id' => $this->enrollment->id,
            'invoice_number' => 'INV-2024-001',
            'amount' => 150000,
            'currency' => 'XAF',
            'status' => 'paid'
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 150000,
            'currency' => 'XAF',
            'reference' => 'MOMO123456789',
            'status' => 'confirmed'
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/payments/{$payment->id}/receipt");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test de validation des montants de paiement
     */
    public function test_payment_amount_validation(): void
    {
        $invoice = Invoice::create([
            'enrollement_id' => $this->enrollment->id,
            'invoice_number' => 'INV-2024-001',
            'amount' => 150000,
            'currency' => 'XAF',
            'status' => 'pending'
        ]);

        Sanctum::actingAs($this->student);

        // Test avec montant incorrect
        $response = $this->postJson('/api/payments', [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 100000, // Montant inférieur à la facture
            'reference' => 'MOMO123456789'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }
}