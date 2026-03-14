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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Étudiant
            $table->foreignId('enrollement_id')->constrained('enrollements')->onDelete('cascade'); // Enrôlement
            $table->string('invoice_number')->unique(); // Numéro de facture (ex: INV-2026-001)
            $table->string('title')->default('Frais d\'enrôlement académique'); // Titre de la facture
            $table->text('description')->nullable(); // Description détaillée
            $table->decimal('subtotal', 10, 2); // Sous-total
            $table->decimal('tax_amount', 10, 2)->default(0); // Montant des taxes
            $table->decimal('total_amount', 10, 2); // Montant total
            $table->string('currency', 3)->default('XOF'); // Devise (Franc CFA)
            $table->date('issue_date'); // Date d'émission
            $table->date('due_date'); // Date d'échéance
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->string('pdf_path')->nullable(); // Chemin du PDF généré
            $table->json('line_items')->nullable(); // Détail des lignes de facturation
            $table->text('notes')->nullable(); // Notes additionnelles
            $table->timestamp('sent_at')->nullable(); // Date d'envoi
            $table->timestamp('paid_at')->nullable(); // Date de paiement
            $table->timestamps();
            
            // Index pour les recherches fréquentes
            $table->index(['user_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};