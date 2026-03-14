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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom affiché (ex: "Orange Money")
            $table->string('code')->unique(); // Code technique (ex: "orange_money")
            $table->string('type')->default('mobile_money'); // Type: mobile_money, bank_transfer, card, cash
            $table->text('description')->nullable(); // Description de la méthode
            $table->json('configuration')->nullable(); // Configuration spécifique (numéros, frais, etc.)
            $table->boolean('is_active')->default(true); // Méthode active ou non
            $table->decimal('min_amount', 10, 2)->default(0); // Montant minimum
            $table->decimal('max_amount', 10, 2)->nullable(); // Montant maximum
            $table->decimal('fee_percentage', 5, 2)->default(0); // Frais en pourcentage
            $table->decimal('fee_fixed', 10, 2)->default(0); // Frais fixes
            $table->integer('sort_order')->default(0); // Ordre d'affichage
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};