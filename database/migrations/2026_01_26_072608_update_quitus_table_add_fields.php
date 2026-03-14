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
        Schema::table('quitus', function (Blueprint $table) {
            $table->unsignedBigInteger('enrollement_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('annee_academique_id')->nullable()->after('enrollement_id');
            $table->string('pdf_path')->nullable()->after('statut');
            
            // Ajouter les clés étrangères
            $table->foreign('enrollement_id')->references('id')->on('enrollements')->onDelete('set null');
            $table->foreign('annee_academique_id')->references('id')->on('academic_years')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quitus', function (Blueprint $table) {
            $table->dropForeign(['enrollement_id']);
            $table->dropForeign(['annee_academique_id']);
            $table->dropColumn(['enrollement_id', 'annee_academique_id', 'pdf_path']);
        });
    }
};
