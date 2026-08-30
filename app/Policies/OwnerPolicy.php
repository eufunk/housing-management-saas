<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Owner;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOrganizationAccess;

class OwnerPolicy
{
    use AuthorizesOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager)
            || $this->hasRole($user, OrganizationRole::Owner);
    }

    public function view(User $user, Owner $owner): bool
    {
        if (! $this->belongsToUserOrganization($user, $owner)) {
            return false;
        }

        if ($this->hasRole($user, OrganizationRole::PropertyManager)) {
            return true;
        }

        return $this->hasRole($user, OrganizationRole::Owner)
            && $owner->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager);
    }

    public function update(User $user, Owner $owner): bool
    {
        return $this->belongsToUserOrganization($user, $owner)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }

    public function delete(User $user, Owner $owner): bool
    {
        return $this->belongsToUserOrganization($user, $owner)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }
}
