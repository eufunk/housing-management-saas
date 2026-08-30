<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Contractor;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOrganizationAccess;

class ContractorPolicy
{
    use AuthorizesOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager)
            || $this->hasRole($user, OrganizationRole::Contractor);
    }

    public function view(User $user, Contractor $contractor): bool
    {
        if (! $this->belongsToUserOrganization($user, $contractor)) {
            return false;
        }

        if ($this->hasRole($user, OrganizationRole::PropertyManager)) {
            return true;
        }

        return $this->hasRole($user, OrganizationRole::Contractor)
            && $contractor->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager);
    }

    public function update(User $user, Contractor $contractor): bool
    {
        return $this->belongsToUserOrganization($user, $contractor)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }

    public function delete(User $user, Contractor $contractor): bool
    {
        return $this->belongsToUserOrganization($user, $contractor)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }
}
