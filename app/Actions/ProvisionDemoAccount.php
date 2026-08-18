<?php

namespace App\Actions;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Provides the single, shared guest-demo account used by the public "Demo
 * ausprobieren" button on the landing page. Idempotent: every call returns
 * the same organization/user, (re-)creating either if they were ever
 * deleted, so the demo login self-heals without manual intervention.
 *
 * The demo account is intentionally shared across all visitors rather than
 * provisioned per-session — there is no mutable business data behind it yet
 * (every module page is a read-only placeholder), so visitors cannot affect
 * each other. Revisit this once real CRUD exists for the demoed modules.
 */
class ProvisionDemoAccount
{
    public const DEMO_EMAIL = 'demo@immodesk.app';

    public function handle(): User
    {
        $organization = Organization::firstOrCreate(
            ['slug' => 'demo'],
            ['name' => 'Demo Hausverwaltung'],
        );

        $user = User::firstOrCreate(
            ['email' => self::DEMO_EMAIL],
            [
                'name' => 'Demo-Zugang',
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'current_organization_id' => $organization->id,
            ],
        );

        if ($user->current_organization_id !== $organization->id) {
            $user->forceFill(['current_organization_id' => $organization->id])->save();
        }

        if (! $organization->users()->where('users.id', $user->id)->exists()) {
            $organization->users()->attach($user, ['role' => OrganizationRole::PropertyManager->value]);
        }

        return $user;
    }
}
