<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOrganizationAccess;

class MaintenanceRequestPolicy
{
    use AuthorizesOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        if (! $this->belongsToUserOrganization($user, $maintenanceRequest)) {
            return false;
        }

        if ($this->hasRole($user, OrganizationRole::PropertyManager)) {
            return true;
        }

        if ($this->hasRole($user, OrganizationRole::Tenant)) {
            return $maintenanceRequest->tenant?->user_id === $user->id;
        }

        if ($this->hasRole($user, OrganizationRole::Contractor)) {
            return $maintenanceRequest->contractor?->user_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user, OrganizationRole::PropertyManager)
            || $this->hasRole($user, OrganizationRole::Tenant);
    }

    public function update(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        if (! $this->belongsToUserOrganization($user, $maintenanceRequest)) {
            return false;
        }

        if ($this->hasRole($user, OrganizationRole::PropertyManager)) {
            return true;
        }

        // Contractors may update status/notes on requests assigned to them,
        // but may not reassign or delete the request itself.
        return $this->hasRole($user, OrganizationRole::Contractor)
            && $maintenanceRequest->contractor?->user_id === $user->id;
    }

    public function delete(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        return $this->belongsToUserOrganization($user, $maintenanceRequest)
            && $this->hasRole($user, OrganizationRole::PropertyManager);
    }
}
