<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Auto-assign a role when a user is created:
     *  - linked to a member  → 'member'
     *  - standalone staff    → 'treasurer' by default
     * The admin seeder/admins can override this.
     */
    public function created(User $user): void
    {
        if ($user->hasAnyRole(['admin', 'treasurer', 'member'])) {
            return;
        }

        $role = $user->member_id ? 'member' : 'treasurer';
        $user->assignRole($role);
    }

    /**
     * Keep roles in sync if a user is later linked to (or unlinked from) a member.
     */
    public function updated(User $user): void
    {
        if (! $user->wasChanged('member_id')) {
            return;
        }

        if ($user->member_id && ! $user->hasRole('member')) {
            $user->syncRoles(['member']);
        }
    }
}
