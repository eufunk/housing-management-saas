<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Owner;
use App\Models\User;

test('a property manager can view, update and delete owners in their organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $owner = Owner::factory()->for($organization)->create();

    expect($manager->can('view', $owner))->toBeTrue();
    expect($manager->can('update', $owner))->toBeTrue();
    expect($manager->can('delete', $owner))->toBeTrue();
    expect($manager->can('create', Owner::class))->toBeTrue();
});

test('an owner can only view their own owner record, not manage it', function () {
    $organization = Organization::factory()->create();
    $ownerUser = memberOf($organization, OrganizationRole::Owner);

    $ownRecord = Owner::factory()->for($organization)->create(['user_id' => $ownerUser->id]);
    $otherRecord = Owner::factory()->for($organization)->create();

    expect($ownerUser->can('view', $ownRecord))->toBeTrue();
    expect($ownerUser->can('view', $otherRecord))->toBeFalse();
    expect($ownerUser->can('update', $ownRecord))->toBeFalse();
});

test('a tenant cannot manage owners', function () {
    $organization = Organization::factory()->create();
    $tenant = memberOf($organization, OrganizationRole::Tenant);
    $owner = Owner::factory()->for($organization)->create();

    expect($tenant->can('view', $owner))->toBeFalse();
    expect($tenant->can('create', Owner::class))->toBeFalse();
});

test('a property manager from another organization cannot access the owner', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $ownerA = Owner::factory()->for($organizationA)->create();

    expect($managerB->can('view', $ownerA))->toBeFalse();
    expect($managerB->can('update', $ownerA))->toBeFalse();
});

test('a super admin can manage any owner regardless of organization', function () {
    $organization = Organization::factory()->create();
    $owner = Owner::factory()->for($organization)->create();

    $superAdmin = User::factory()->create(['is_super_admin' => true]);

    expect($superAdmin->can('view', $owner))->toBeTrue();
    expect($superAdmin->can('update', $owner))->toBeTrue();
    expect($superAdmin->can('delete', $owner))->toBeTrue();
});
