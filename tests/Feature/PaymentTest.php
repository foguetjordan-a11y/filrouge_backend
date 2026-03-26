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

    private function createTestData(): void
    {
        $departement = Departement::create([
            'nom'         => 'Informatique',
            'code'        => 'INFO',
            'description' => 'Departement Informatique',
        ]);

        $filiere = Filiere::create([
            'nom'            => 'Genie Logiciel',
            'code'           => 'GL',
            'departement_id' => $departement->id,
            'description'    => 'Formation en developpement logiciel',
        ]);

        $niveau = Niveau::create([
            'libelle'          => 'Licence 1',
            'nom'              => 'Licence 1',
            'code'             => 'L1',
            'filiere_id'       => $filiere->id,
            'frais_inscription' => 150000,
        ]);

        $academicYear = AcademicYear::create([
            'name'       => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date'   => '2025-06-30',
            'is_active'  => true,
        ]);

        $this->student = User::factory()->create([
            'role'    => 'etudiant',
            'role_id' => 3,
            'status'  => 'approved',
        ]);

        $this->admin = User::factory()->create([
            'role'    => 'admin',
            'role_id' => 1,
            'status'  => 'approved',
        ]);

        $this->enrollment = Enrollement::create([
            'user_id'          => $this->student->id,
            'filiere_id'       => $filiere->id,
            'niveau_id'        => $niveau->id,
            'academic_year_id' => $academicYear->id,
            'nom'              => 'Doe',
            'prenom'           => 'John',
            'date_naissance'   => '2000-01-01',
            'lieu_naissance'   => 'Yaounde',
            'telephone'        => '237123456789',
            'adresse'          => '123 Rue Test',
            'status'           => 'approved',
        ]);

        $this->paymentMethod = PaymentMethod::create([
            'name'        => 'Mobile Money',
            'code'        => 'MOMO',
            'type'        => 'mobile_money',
            'is_active'   => true,
            'description' => 'Paiement par Mobile Money',
            'fee_percentage' => 0,
            'fee_fixed'      => 0,
            'min_amount'     => 0,
        ]);
    }

    /** Test que les methodes de paiement sont accessibles */
    public function test_payment_methods_are_accessible(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/payment-methods');

        $response->assertStatus(200);
    }

    /** Test que l'etudiant peut voir ses paiements */
    public function test_student_can_view_payments(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/payments');

        $response->assertStatus(200);
    }

    /** Test que l'admin peut voir tous les paiements */
    public function test_admin_can_view_all_payments(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/payments');

        $response->assertStatus(200);
    }

    /** Test que l'admin peut voir les paiements en attente de verification */
    public function test_admin_can_view_pending_verification(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/payments/pending-verification');

        $response->assertStatus(200);
    }

    /** Test de creation d'une facture (Invoice model) */
    public function test_invoice_model_creation(): void
    {
        $invoice = Invoice::create([
            'user_id'        => $this->student->id,
            'enrollement_id' => $this->enrollment->id,
            'invoice_number' => 'INV-2024-001',
            'title'          => "Frais d'inscription",
            'subtotal'       => 150000,
            'tax_amount'     => 0,
            'total_amount'   => 150000,
            'currency'       => 'XOF',
            'issue_date'     => now()->toDateString(),
            'due_date'       => now()->addDays(30)->toDateString(),
            'status'         => 'sent',
        ]);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals('INV-2024-001', $invoice->invoice_number);
        $this->assertEquals(150000, $invoice->total_amount);
        $this->assertEquals('sent', $invoice->status);
    }

    /** Test de creation d'un paiement (Payment model) */
    public function test_payment_model_creation(): void
    {
        $invoice = Invoice::create([
            'user_id'        => $this->student->id,
            'enrollement_id' => $this->enrollment->id,
            'invoice_number' => 'INV-2024-001',
            'title'          => "Frais d'inscription",
            'subtotal'       => 150000,
            'tax_amount'     => 0,
            'total_amount'   => 150000,
            'currency'       => 'XOF',
            'issue_date'     => now()->toDateString(),
            'due_date'       => now()->addDays(30)->toDateString(),
            'status'         => 'sent',
        ]);

        $payment = Payment::create([
            'user_id'           => $this->student->id,
            'enrollement_id'    => $this->enrollment->id,
            'invoice_id'        => $invoice->id,
            'payment_method_id' => $this->paymentMethod->id,
            'payment_reference' => 'PAY-2024-0001',
            'amount'            => 150000,
            'fee_amount'        => 0,
            'net_amount'        => 150000,
            'currency'          => 'XOF',
            'status'            => 'pending',
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals('PAY-2024-0001', $payment->payment_reference);
        $this->assertEquals('pending', $payment->status);
        $this->assertTrue($payment->isPending());
    }

    /** Test que l'etudiant ne peut pas acceder aux routes admin de paiement */
    public function test_student_cannot_access_admin_payment_routes(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/admin/payments');

        $response->assertStatus(403);
    }

    /** Test que l'admin peut voir les statistiques de paiement */
    public function test_admin_can_view_payment_statistics(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/payments/statistics');

        $response->assertStatus(200);
    }
}
