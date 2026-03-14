<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Étudiant
            $table->foreignId('enrollement_id')->constrained('enrollements')->onDelete('cascade'); // Enrôlement
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null'); // Facture associée
            $table->foreignId('payment_method_id')->constrained()->onDelete('restrict'); // Méthode de paiement
            $table->string('payment_reference')->unique(); // Référence unique (ex: PAY-2026-001)
            $table->decimal('amount', 10, 2); // Montant payé
            $table->decimal('fee_amount', 10, 2)->default(0); // Frais de transaction
            $table->decimal('net_amount', 10, 2); // Montant net reçu
            $table->string('currency', 3)->default('XOF'); // Devise
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->string('transaction_id')->nullable(); // ID de transaction externe
            $table->string('external_reference')->nullable(); // Référence externe (banque, mobile money)
            $table->json('payment_details')->nullable(); // Détails spécifiques au mode de paiement
            $table->text('notes')->nullable(); // Notes administratives
            $table->string('receipt_path')->nullable(); // Chemin du reçu PDF
            $table->timestamp('submitted_at')->nullable(); // Date de soumission
            $table->timestamp('processed_at')->nullable(); // Date de traitement
            $table->timestamp('completed_at')->nullable(); // Date de finalisation
            $table->timestamp('failed_at')->nullable(); // Date d'échec
            $table->text('failure_reason')->nullable(); // Raison de l'échec
            $table->timestamps();
            
            // Index pour les recherches fréquentes
            $table->index(['user_id', 'status']);
            $table->index(['payment_reference']);
            $table->index(['status', 'created_at']);
            $table->index(['transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};