<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('enrollements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filiere_id')->constrained()->cascadeOnDelete();
            $table->foreignId('niveau_id')->constrained()->cascadeOnDelete();
            $table->foreignId('annee_academique_id')->nullable()->constrained('academic_years')->nullOnDelete();

            $table->date('date_enrollement')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');

            $table->timestamps();

            // ❌ empêche double enrôlement même année
            $table->unique(
                ['user_id', 'filiere_id', 'annee_academique_id'],
                'unique_enrollement_etudiant'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollements');
    }
};

