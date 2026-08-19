<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Building;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOrganizationAccess;

class BuildingPolicy
{
    use AuthorizesOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager)
            || $this->hasRole($user, OrganizationRole::Owner);
    }

    public function view(User $user, Building $building): bool
    {
        if (! $this->belongsToUserOrganization($user, $building)) {
            return false;
        }

        if ($this->hasRole($user, OrganizationRole::PropertyManager)) {
            return true;
        }

        return $this->hasRole($user, OrganizationRole::Owner)
            && $building->property?->owner?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager);
    }

    public function update(User $user, Building $building): bool
    {
        return $this->belongsToUserOrganization($user, $building)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }

    public function delete(User $user, Building $building): bool
    {
        return $this->belongsToUserOrganization($user, $building)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }
}
