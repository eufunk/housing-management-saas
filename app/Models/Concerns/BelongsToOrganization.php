<?php

namespace App\Models\Concerns;

use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Marks a model as tenant-scoped: every query is automatically constrained
 * to the authenticated user's current organization (see OrganizationScope),
 * and new records are stamped with that organization on creation.
 */
trait BelongsToOrganization
{
    protected static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope);

        static::creating(function (Model $model) {
            if (empty($model->organization_id) && auth()->hasUser()) {
                $model->organization_id = auth()->user()->current_organization_id;
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
