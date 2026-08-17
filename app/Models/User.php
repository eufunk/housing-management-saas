<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\OrganizationRole;
use App\Models\Concerns\HasUlid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlid, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_organization_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function currentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'current_organization_id');
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * The user's role within a given organization, or their currently
     * active organization when none is specified.
     */
    public function roleIn(?Organization $organization = null): ?OrganizationRole
    {
        $organization ??= $this->currentOrganization;

        if ($organization === null) {
            return null;
        }

        $pivot = $this->organizations()
            ->where('organizations.id', $organization->id)
            ->first()
            ?->pivot;

        return $pivot ? OrganizationRole::from($pivot->role) : null;
    }

    public function hasRoleIn(OrganizationRole $role, ?Organization $organization = null): bool
    {
        return $this->roleIn($organization) === $role;
    }
}
