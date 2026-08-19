<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Unit;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOrganizationAccess;

class UnitPolicy
{
    use AuthorizesOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager)
            || $this->hasRole($user, OrganizationRole::Owner);
    }

    public function view(User $user, Unit $unit): bool
    {
        if (! $this->belongsToUserOrganization($user, $unit)) {
            return false;
        }

        if ($this->hasRole($user, OrganizationRole::PropertyManager)) {
            return true;
        }

        return $this->hasRole($user, OrganizationRole::Owner)
            && $unit->building?->property?->owner?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager);
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->belongsToUserOrganization($user, $unit)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $this->belongsToUserOrganization($user, $unit)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }
}
