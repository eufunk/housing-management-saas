<?php

namespace App\Policies\Concerns;

use App\Enums\OrganizationRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared authorization building blocks for organization-scoped policies.
 *
 * OrganizationScope already prevents cross-tenant records from being loaded
 * at all, but a policy still needs to re-check the organization explicitly:
 * route-model binding, cached instances, or relations eager-loaded before a
 * scope was applied can all hand a policy a model from another tenant.
 */
trait AuthorizesOrganizationAccess
{
    protected function belongsToUserOrganization(User $user, Model $model): bool
    {
        return $user->current_organization_id !== null
            && $user->current_organization_id === $model->organization_id;
    }

    protected function hasRole(User $user, OrganizationRole $role): bool
    {
        return $user->hasRoleIn($role);
    }
}
