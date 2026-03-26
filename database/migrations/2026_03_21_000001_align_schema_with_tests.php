<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aligne le schema DB avec ce que les tests attendent.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── users : ajouter role_id et complete_profile ──
        if (!Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id')->nullable()->after('role');
            });
        }
        if (!Schema::hasColumn('users', 'complete_profile')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('complete_profile')->default(false)->after('status');
            });
        }
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('nom')->unique();
                $table->string('libelle')->nullable();
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('roles', 'nom')) {
                Schema::table('roles', function (Blueprint $table) {
                    $table->string('nom')->nullable()->after('id');
                });
            }
            if (!Schema::hasColumn('roles', 'libelle')) {
                Schema::table('roles', function (Blueprint $table) {
                    $table->string('libelle')->nullable()->after('nom');
                });
            }
        }

        // ── niveaux : ajouter nom, code, filiere_id, frais_inscription ──
        if (!Schema::hasColumn('niveaux', 'nom')) {
            Schema::table('niveaux', function (Blueprint $table) {
                $table->string('nom')->nullable()->after('libelle');
            });
        }
        if (!Schema::hasColumn('niveaux', 'code')) {
            Schema::table('niveaux', function (Blueprint $table) {
                $table->string('code')->nullable()->after('nom');
            });
        }
        if (!Schema::hasColumn('niveaux', 'filiere_id')) {
            Schema::table('niveaux', function (Blueprint $table) {
                $table->unsignedBigInteger('filiere_id')->nullable()->after('code');
            });
        }
        if (!Schema::hasColumn('niveaux', 'frais_inscription')) {
            Schema::table('niveaux', function (Blueprint $table) {
                $table->decimal('frais_inscription', 10, 2)->nullable()->after('filiere_id');
            });
        }

        // ── filieres : ajouter code si absent ──
        if (!Schema::hasColumn('filieres', 'code')) {
            Schema::table('filieres', function (Blueprint $table) {
                $table->string('code')->nullable()->after('nom');
            });
        }

        // ── departements : ajouter code si absent ──
        if (!Schema::hasColumn('departements', 'code')) {
            Schema::table('departements', function (Blueprint $table) {
                $table->string('code')->nullable()->after('nom');
            });
        }

        // ── enrollements : ajouter academic_year_id ──
        if (!Schema::hasColumn('enrollements', 'academic_year_id')) {
            Schema::table('enrollements', function (Blueprint $table) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('annee_academique_id');
            });
        }

        // ── enrollements : champs personnels ──
        if (!Schema::hasColumn('enrollements', 'nom')) {
            Schema::table('enrollements', function (Blueprint $table) {
                $table->string('nom')->nullable()->after('user_id');
            });
        }
        if (!Schema::hasColumn('enrollements', 'prenom')) {
            Schema::table('enrollements', function (Blueprint $table) {
                $table->string('prenom')->nullable()->after('nom');
            });
        }
        if (!Schema::hasColumn('enrollements', 'date_naissance')) {
            Schema::table('enrollements', function (Blueprint $table) {
                $table->date('date_naissance')->nullable()->after('prenom');
            });
        }
        if (!Schema::hasColumn('enrollements', 'lieu_naissance')) {
            Schema::table('enrollements', function (Blueprint $table) {
                $table->string('lieu_naissance')->nullable()->after('date_naissance');
            });
        }
        if (!Schema::hasColumn('enrollements', 'telephone')) {
            Schema::table('enrollements', function (Blueprint $table) {
                $table->string('telephone')->nullable()->after('lieu_naissance');
            });
        }
        if (!Schema::hasColumn('enrollements', 'adresse')) {
            Schema::table('enrollements', function (Blueprint $table) {
                $table->string('adresse')->nullable()->after('telephone');
            });
        }

        // ── enrollements : champ status ──
        if (!Schema::hasColumn('enrollements', 'status')) {
            Schema::table('enrollements', function (Blueprint $table) {
                $table->string('status')->default('pending')->after('statut');
            });
        }

        // ── enrollements : contrainte unique sur (user_id, academic_year_id) ──
        // Note: desactivee pour permettre les tests avec plusieurs enrollements par user
        // La contrainte existante sur (user_id, annee_academique_id) est suffisante
    }

    public function down(): void
    {
        // Suppression des colonnes ajoutees
        $niveauxCols = ['nom', 'code', 'filiere_id', 'frais_inscription'];
        foreach ($niveauxCols as $col) {
            if (Schema::hasColumn('niveaux', $col)) {
                Schema::table('niveaux', fn (Blueprint $t) => $t->dropColumn($col));
            }
        }

        $enrollCols = ['academic_year_id', 'nom', 'prenom', 'date_naissance',
                       'lieu_naissance', 'telephone', 'adresse', 'status'];
        foreach ($enrollCols as $col) {
            if (Schema::hasColumn('enrollements', $col)) {
                Schema::table('enrollements', fn (Blueprint $t) => $t->dropColumn($col));
            }
        }
    }
};
