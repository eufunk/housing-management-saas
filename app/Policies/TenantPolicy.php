<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOrganizationAccess;

class TenantPolicy
{
    use AuthorizesOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager)
            || $this->hasRole($user, OrganizationRole::Tenant);
    }

    public function view(User $user, Tenant $tenant): bool
    {
        if (! $this->belongsToUserOrganization($user, $tenant)) {
            return false;
        }

        if ($this->hasRole($user, OrganizationRole::PropertyManager)) {
            return true;
        }

        return $this->hasRole($user, OrganizationRole::Tenant)
            && $tenant->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager);
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $this->belongsToUserOrganization($user, $tenant)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $this->belongsToUserOrganization($user, $tenant)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }
}
