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
        Schema::table('users', function (Blueprint $table) {
            // Informations personnelles complètes
            $table->string('nom')->nullable()->after('name');
            $table->string('prenom')->nullable()->after('nom');
            $table->enum('sexe', ['M', 'F'])->nullable()->after('prenom');
            $table->date('date_naissance')->nullable()->after('sexe');
            $table->string('lieu_naissance')->nullable()->after('date_naissance');
            $table->string('nationalite')->default('Sénégalaise')->after('lieu_naissance');
            $table->text('adresse')->nullable()->after('nationalite');
            $table->string('telephone')->nullable()->after('adresse');
            $table->string('photo_identite')->nullable()->after('telephone');
            $table->string('numero_cni')->nullable()->after('photo_identite');
            $table->string('numero_passeport')->nullable()->after('numero_cni');
            
            // Informations académiques
            $table->string('matricule')->unique()->nullable()->after('numero_passeport');
            $table->enum('type_inscription', ['nouvelle', 'reinscription'])->default('nouvelle')->after('matricule');
            $table->timestamp('matricule_generated_at')->nullable()->after('type_inscription');
            $table->timestamp('profile_completed_at')->nullable()->after('matricule_generated_at');
            $table->boolean('is_profile_complete')->default(false)->after('profile_completed_at');
            
            // Index pour optimiser les recherches
            $table->index(['role', 'status']);
            $table->index(['matricule']);
            $table->index(['is_profile_complete']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nom', 'prenom', 'sexe', 'date_naissance', 'lieu_naissance', 
                'nationalite', 'adresse', 'telephone', 'photo_identite', 
                'numero_cni', 'numero_passeport', 'matricule', 'type_inscription',
                'matricule_generated_at', 'profile_completed_at', 'is_profile_complete'
            ]);
        });
    }
};