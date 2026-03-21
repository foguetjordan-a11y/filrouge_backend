<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aligne le schéma DB avec ce que les tests attendent.
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
                $table->boolean('complete_profile')->default(false)->after('is_profile_complete');
            });
        }

        // ── table roles custom (nom + libelle) ──
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('nom')->unique();
                $table->string('libelle')->nullable();
                $table->timestamps();
            });
        } else {
            // La table existe (Spatie), on ajoute nom et libelle si absents
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
        Schema::table('niveaux', function (Blueprint $table) {
            $table->string('nom')->nullable()->after('libelle');
            $table->string('code')->nullable()->after('nom');
            $table->unsignedBigInteger('filiere_id')->nullable()->after('code');
            $table->decimal('frais_inscription', 10, 2)->nullable()->after('filiere_id');

            $table->foreign('filiere_id')->references('id')->on('filieres')->onDelete('set null');
        });

        // ── filieres : ajouter code si absent ──
        if (!Schema::hasColumn('filieres', 'code')) {
            Schema::table('filieres', function (Blueprint $table) {
                $table->string('code')->nullable()->after('nom');
            });
        }

        // ── enrollements : ajouter les champs attendus par les tests ──
        Schema::table('enrollements', function (Blueprint $table) {
            // Clé étrangère academic_year_id (alias de annee_academique_id)
            if (!Schema::hasColumn('enrollements', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('annee_academique_id');
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null');
            }

            // Champs personnels de l'étudiant dans l'enrollement
            if (!Schema::hasColumn('enrollements', 'nom')) {
                $table->string('nom')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('enrollements', 'prenom')) {
                $table->string('prenom')->nullable()->after('nom');
            }
            if (!Schema::hasColumn('enrollements', 'date_naissance')) {
                $table->date('date_naissance')->nullable()->after('prenom');
            }
            if (!Schema::hasColumn('enrollements', 'lieu_naissance')) {
                $table->string('lieu_naissance')->nullable()->after('date_naissance');
            }
            if (!Schema::hasColumn('enrollements', 'telephone')) {
                $table->string('telephone')->nullable()->after('lieu_naissance');
            }
            if (!Schema::hasColumn('enrollements', 'adresse')) {
                $table->string('adresse')->nullable()->after('telephone');
            }

            // Champ status (alias de statut avec valeurs en anglais)
            if (!Schema::hasColumn('enrollements', 'status')) {
                $table->string('status')->default('pending')->after('statut');
            }
        });
    }

    public function down(): void
    {
        Schema::table('niveaux', function (Blueprint $table) {
            $table->dropForeign(['filiere_id']);
            $table->dropColumn(['nom', 'code', 'filiere_id', 'frais_inscription']);
        });

        Schema::table('enrollements', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn(['academic_year_id', 'nom', 'prenom', 'date_naissance',
                'lieu_naissance', 'telephone', 'adresse', 'status']);
        });
    }
};
