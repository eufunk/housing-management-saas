<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Property;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOrganizationAccess;

class PropertyPolicy
{
    use AuthorizesOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager)
            || $this->hasRole($user, OrganizationRole::Owner);
    }

    public function view(User $user, Property $property): bool
    {
        if (! $this->belongsToUserOrganization($user, $property)) {
            return false;
        }

        if ($this->hasRole($user, OrganizationRole::PropertyManager)) {
            return true;
        }

        return $this->hasRole($user, OrganizationRole::Owner)
            && $property->owner?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager);
    }

    public function update(User $user, Property $property): bool
    {
        return $this->belongsToUserOrganization($user, $property)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }

    public function delete(User $user, Property $property): bool
    {
        return $this->belongsToUserOrganization($user, $property)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }
}
