<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, HasUlid, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function usersWithRole(OrganizationRole $role): BelongsToMany
    {
        return $this->users()->wherePivot('role', $role->value);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function owners(): HasMany
    {
        return $this->hasMany(Owner::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function contractors(): HasMany
    {
        return $this->hasMany(Contractor::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
