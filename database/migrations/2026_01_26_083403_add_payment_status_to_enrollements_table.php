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
            $table->enum('payment_status', ['not_required', 'pending', 'paid', 'failed'])
                  ->default('not_required')
                  ->after('statut')
                  ->comment('Statut du paiement: not_required=pas requis, pending=en attente, paid=payé, failed=échoué');
            
            $table->decimal('payment_amount', 10, 2)
                  ->nullable()
                  ->after('payment_status')
                  ->comment('Montant du paiement requis');
            
            $table->timestamp('payment_due_date')
                  ->nullable()
                  ->after('payment_amount')
                  ->comment('Date limite de paiement');
            
            $table->timestamp('paid_at')
                  ->nullable()
                  ->after('payment_due_date')
                  ->comment('Date de paiement effectif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollements', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_amount', 'payment_due_date', 'paid_at']);
        });
    }
};