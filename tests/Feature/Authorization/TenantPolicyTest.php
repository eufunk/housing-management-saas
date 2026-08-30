<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;

test('a property manager can view, update and delete tenants in their organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $tenant = Tenant::factory()->for($organization)->create();

    expect($manager->can('view', $tenant))->toBeTrue();
    expect($manager->can('update', $tenant))->toBeTrue();
    expect($manager->can('delete', $tenant))->toBeTrue();
    expect($manager->can('create', Tenant::class))->toBeTrue();
});

test('a tenant can only view their own tenant record, not manage it', function () {
    $organization = Organization::factory()->create();
    $tenantUser = memberOf($organization, OrganizationRole::Tenant);

    $ownRecord = Tenant::factory()->for($organization)->create(['user_id' => $tenantUser->id]);
    $otherRecord = Tenant::factory()->for($organization)->create();

    expect($tenantUser->can('view', $ownRecord))->toBeTrue();
    expect($tenantUser->can('view', $otherRecord))->toBeFalse();
    expect($tenantUser->can('update', $ownRecord))->toBeFalse();
});

test('an owner cannot manage tenants', function () {
    $organization = Organization::factory()->create();
    $ownerUser = memberOf($organization, OrganizationRole::Owner);
    $tenant = Tenant::factory()->for($organization)->create();

    expect($ownerUser->can('view', $tenant))->toBeFalse();
    expect($ownerUser->can('create', Tenant::class))->toBeFalse();
});

test('a property manager from another organization cannot access the tenant', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $tenantA = Tenant::factory()->for($organizationA)->create();

    expect($managerB->can('view', $tenantA))->toBeFalse();
    expect($managerB->can('update', $tenantA))->toBeFalse();
});

test('a super admin can manage any tenant regardless of organization', function () {
    $organization = Organization::factory()->create();
    $tenant = Tenant::factory()->for($organization)->create();

    $superAdmin = User::factory()->create(['is_super_admin' => true]);

    expect($superAdmin->can('view', $tenant))->toBeTrue();
    expect($superAdmin->can('update', $tenant))->toBeTrue();
    expect($superAdmin->can('delete', $tenant))->toBeTrue();
});
