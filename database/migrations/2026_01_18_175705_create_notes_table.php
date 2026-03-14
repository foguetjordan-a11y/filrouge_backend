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
    {Schema::create('notes', function (Blueprint $table) {
    $table->id();

    $table->foreignId('enrollement_id')->constrained()->cascadeOnDelete();
    $table->foreignId('matiere_id')->constrained()->cascadeOnDelete();

    $table->float('note', 5, 2); // ex : 14.50

    $table->timestamps();

    // Une seule note par matière et enrôlement
    $table->unique(['enrollement_id', 'matiere_id']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
