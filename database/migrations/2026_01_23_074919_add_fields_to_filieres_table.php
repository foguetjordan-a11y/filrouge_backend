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
        Schema::table('filieres', function (Blueprint $table) {
            $table->text('description')->nullable()->after('nom');
            $table->string('duree_etudes')->nullable()->after('description');
            $table->string('diplome_delivre')->nullable()->after('duree_etudes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filieres', function (Blueprint $table) {
            $table->dropColumn(['description', 'duree_etudes', 'diplome_delivre']);
        });
    }
};