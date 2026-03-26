<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Augmenter la taille de currency pour supporter FCFA (10 chars)
        if (Schema::hasColumn('payments', 'currency')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('currency', 10)->default('XOF')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payments', 'currency')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('currency', 3)->default('XOF')->change();
            });
        }
    }
};
