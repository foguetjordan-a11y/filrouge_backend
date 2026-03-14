<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Roles table is created by Spatie permission package in create_permission_tables migration
        // This migration is deprecated and kept for compatibility
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No operation needed
    }
};
