<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GestionSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions
        $permissions = [
            'gerer utilisateurs',
            'gerer departements',
            'gerer filieres',
            'gerer inscriptions',
            'gerer paiements',
            'consulter documents',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'api']
            );
        }

        // Rôles
        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'api']
        );

        $etudiant = Role::firstOrCreate(
            ['name' => 'etudiant', 'guard_name' => 'api']
        );

        // Attribution permissions
        $admin->syncPermissions($permissions);

        $etudiant->syncPermissions([
            'gerer inscriptions',
            'consulter documents',
        ]);
    }
}
