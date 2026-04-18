<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Three roles for Local Investors:
     *  - admin:     full control (settings, users, everything)
     *  - treasurer: day-to-day operations (contributions, loans, fines, reports)
     *  - member:    self-service (view own statement, apply for a loan)
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // --- Permissions --------------------------------------------------
        $permissions = [
            // Members
            'members.view', 'members.manage',
            // Contributions
            'contributions.view', 'contributions.manage',
            // Loans
            'loans.view', 'loans.apply', 'loans.manage', 'loans.approve', 'loans.disburse', 'loans.repay',
            // Financial ledgers
            'incomes.view', 'incomes.manage',
            'expenditures.view', 'expenditures.manage',
            'withdrawals.view', 'withdrawals.manage',
            'cash-returns.view', 'cash-returns.manage',
            // Reports & settings
            'projects.view', 'projects.manage',
            'reports.view',
            'settings.manage',
            'users.manage',
            // Member self-service
            'portal.access',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // --- Roles --------------------------------------------------------
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $treasurer = Role::firstOrCreate(['name' => 'treasurer', 'guard_name' => 'web']);
        $treasurer->syncPermissions([
            'members.view', 'members.manage',
            'contributions.view', 'contributions.manage',
            'loans.view', 'loans.manage', 'loans.approve', 'loans.disburse', 'loans.repay',
            'incomes.view', 'incomes.manage',
            'expenditures.view', 'expenditures.manage',
            'withdrawals.view', 'withdrawals.manage',
            'cash-returns.view', 'cash-returns.manage',
            'projects.view', 'projects.manage',
            'reports.view',
        ]);

        $member = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        $member->syncPermissions([
            'portal.access',
            'loans.apply',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
