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
            // Informations d'enrôlement étendues
            $table->string('matricule_etudiant')->nullable()->after('user_id');
            $table->enum('type_inscription', ['nouvelle', 'reinscription'])->default('nouvelle')->after('matricule_etudiant');
            
            // Documents soumis
            $table->string('photo_identite')->nullable()->after('photo');
            $table->string('diplome_precedent')->nullable()->after('photo_identite');
            $table->string('releve_notes')->nullable()->after('diplome_precedent');
            $table->string('acte_naissance')->nullable()->after('releve_notes');
            
            // Informations de validation
            $table->timestamp('validated_at')->nullable()->after('statut');
            $table->unsignedBigInteger('validated_by')->nullable()->after('validated_at');
            $table->text('motif_rejet')->nullable()->after('validated_by');
            $table->timestamp('rejected_at')->nullable()->after('motif_rejet');
            
            // Métadonnées
            $table->json('documents_status')->nullable()->after('rejected_at');
            $table->boolean('documents_complete')->default(false)->after('documents_status');
            
            // Clés étrangères
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollements', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropColumn([
                'matricule_etudiant', 'type_inscription', 'photo_identite', 'diplome_precedent',
                'releve_notes', 'acte_naissance', 'validated_at', 'validated_by', 'motif_rejet',
                'rejected_at', 'documents_status', 'documents_complete'
            ]);
        });
    }
};