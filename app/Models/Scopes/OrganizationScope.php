<?php

namespace App\Models\Scopes;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Enforces tenant isolation on every query for organization-scoped models.
 *
 * Fails closed: if no organization context can be resolved for the current
 * request, the query is constrained to a non-existent organization_id
 * rather than falling back to returning every organization's data.
 *
 * @see BelongsToOrganization
 */
class OrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        $user = auth()->user();

        if ($user === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        if ($user->is_super_admin) {
            return;
        }

        $builder->where($model->qualifyColumn('organization_id'), $user->current_organization_id ?? 0);
    }
}
