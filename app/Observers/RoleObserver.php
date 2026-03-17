<?php

namespace App\Observers;

use App\Models\Role;

class RoleObserver
{
    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void
    {
        $this->clearCacheForUsers($role);
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void
    {
        $this->clearCacheForUsers($role);
    }

    /**
     * Handle the Role "restored" event.
     */
    public function restored(Role $role): void
    {
        $this->clearCacheForUsers($role);
    }

    /**
     * Clear the permission cache for all users associated with this role.
     */
    protected function clearCacheForUsers(Role $role): void
    {
        foreach ($role->usuarios as $user) {
            $user->clearPermissionCache();
        }
    }
}
