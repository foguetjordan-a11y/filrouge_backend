<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quitus', function (Blueprint $table) {
            if (!Schema::hasColumn('quitus', 'enrollement_id')) {
                $table->unsignedBigInteger('enrollement_id')->nullable()->after('user_id');
                $table->foreign('enrollement_id')->references('id')->on('enrollements')->onDelete('set null');
            }
            if (!Schema::hasColumn('quitus', 'annee_academique_id')) {
                $table->unsignedBigInteger('annee_academique_id')->nullable()->after('enrollement_id');
                $table->foreign('annee_academique_id')->references('id')->on('academic_years')->onDelete('set null');
            }
            if (!Schema::hasColumn('quitus', 'pdf_path')) {
                $table->string('pdf_path')->nullable()->after('statut');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quitus', function (Blueprint $table) {
            if (Schema::hasColumn('quitus', 'enrollement_id')) {
                $table->dropForeign(['enrollement_id']);
                $table->dropColumn('enrollement_id');
            }
            if (Schema::hasColumn('quitus', 'annee_academique_id')) {
                $table->dropForeign(['annee_academique_id']);
                $table->dropColumn('annee_academique_id');
            }
            if (Schema::hasColumn('quitus', 'pdf_path')) {
                $table->dropColumn('pdf_path');
            }
        });
    }
};
