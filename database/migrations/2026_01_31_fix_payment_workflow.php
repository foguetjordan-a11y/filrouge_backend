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
        Schema::table('payments', function (Blueprint $table) {
            // Champs pour la confirmation par l'étudiant
            $table->timestamp('student_confirmed_at')->nullable()->after('completed_at');
            $table->json('student_confirmation_details')->nullable()->after('student_confirmed_at');
            
            // Champs pour la vérification par l'admin
            $table->timestamp('admin_verified_at')->nullable()->after('student_confirmation_details');
            $table->text('admin_verification_notes')->nullable()->after('admin_verified_at');
            
            // Statut de vérification
            $table->enum('verification_status', [
                'pending',              // En attente de paiement par l'étudiant
                'awaiting_verification', // Étudiant a confirmé, en attente de vérification admin
                'verified',             // Vérifié par l'admin
                'disputed'              // Contesté par l'admin
            ])->default('pending')->after('admin_verification_notes');
            
            // Index pour les requêtes fréquentes
            $table->index(['verification_status', 'created_at']);
            $table->index(['student_confirmed_at']);
            $table->index(['admin_verified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['verification_status', 'created_at']);
            $table->dropIndex(['student_confirmed_at']);
            $table->dropIndex(['admin_verified_at']);
            
            $table->dropColumn([
                'student_confirmed_at',
                'student_confirmation_details',
                'admin_verified_at',
                'admin_verification_notes',
                'verification_status'
            ]);
        });
    }
};