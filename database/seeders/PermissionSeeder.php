<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {

        $permissions = [

            // Dashboard
            'view dashboard',

            // Members
            'view members',
            'create members',
            'edit members',
            'delete members',

            // Contributions
            'view contributions',
            'create contributions',
            'edit contributions',
            'delete contributions',

            // Loans
            'view loans',
            'create loans',
            'edit loans',
            'delete loans',

            // Financial Settings (Penalty Rules, etc.)
            'manage penalty rules',
            'manage settings',

            // Users & Roles
            'view users',
            'create users',
            'edit users',
            'delete users',

            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            'view permissions',
            'assign permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
