<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Only truncate ROLES, DO NOT truncate permissions here
        Schema::disableForeignKeyConstraints();
        Role::truncate();
        Schema::enableForeignKeyConstraints();

        // Create roles
        $admin      = Role::create(['name' => 'admin']);
        $treasurer  = Role::create(['name' => 'treasurer']);
        $secretary  = Role::create(['name' => 'secretary']);
        $member     = Role::create(['name' => 'member']);

        // Assign permissions (created earlier in PermissionSeeder)
        $admin->givePermissionTo(Permission::all());

        $treasurer->givePermissionTo([
            'view members',
            'view contributions',
            'create contributions',
            'edit contributions',
            'view loans',
            'create loans',
        ]);

        $secretary->givePermissionTo([
            'view members',
            'create members',
            'edit members',
            'view contributions',
        ]);

        $member->givePermissionTo([
            'view members',
            'view contributions',
        ]);
    }
}