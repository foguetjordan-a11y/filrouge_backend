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
        Schema::table('enrollements', function (Blueprint $table) {
            // Supprimer les colonnes de documents non nécessaires
            $table->dropColumn([
                'photo_identite',
                'diplome_precedent', 
                'releve_notes',
                'acte_naissance',
                'documents_status',
                'documents_complete'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollements', function (Blueprint $table) {
            // Restaurer les colonnes si nécessaire
            $table->string('photo_identite')->nullable()->after('photo');
            $table->string('diplome_precedent')->nullable()->after('photo_identite');
            $table->string('releve_notes')->nullable()->after('diplome_precedent');
            $table->string('acte_naissance')->nullable()->after('releve_notes');
            $table->json('documents_status')->nullable()->after('rejected_at');
            $table->boolean('documents_complete')->default(false)->after('documents_status');
        });
    }
};