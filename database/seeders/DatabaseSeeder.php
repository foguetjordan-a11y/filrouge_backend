<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1️⃣ Rôles & permissions (Spatie) - DÉSACTIVÉ
        // $this->call(GestionSeeder::class);

        // 2️⃣ Départements
        $this->call(DepartementSeeder::class);

        // 3️⃣ Filières (dépend des départements)
        $this->call(FiliereSeeder::class);

        // 4️⃣ Utilisateurs (dépend des rôles)
        $this->call(UserSeeder::class);

        $this->call(AdminSeeder::class);

        // $this->call(RoleSeeder::class);
    }
}
