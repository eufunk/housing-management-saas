<?php

use App\Enums\OrganizationRole;
use App\Models\Building;
use App\Models\Organization;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;

test('a property manager can view, update and delete units in their organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $unit = Unit::factory()->for($organization)->create();

    expect($manager->can('view', $unit))->toBeTrue();
    expect($manager->can('update', $unit))->toBeTrue();
    expect($manager->can('delete', $unit))->toBeTrue();
    expect($manager->can('create', Unit::class))->toBeTrue();
});

test('an owner can only view units of their own property, not manage them', function () {
    $organization = Organization::factory()->create();
    $ownerUser = memberOf($organization, OrganizationRole::Owner);

    $owner = Owner::factory()->for($organization)->create(['user_id' => $ownerUser->id]);
    $ownProperty = Property::factory()->for($organization)->create(['owner_id' => $owner->id]);
    $ownBuilding = Building::factory()->for($organization)->for($ownProperty)->create();
    $ownUnit = Unit::factory()->for($organization)->for($ownBuilding)->create();
    $otherUnit = Unit::factory()->for($organization)->create();

    // UnitPolicy::view() loads $unit->building->property->owner, which —
    // like every organization-scoped relation — is filtered by
    // OrganizationScope based on the authenticated user, so it must
    // actually be resolvable here.
    $this->actingAs($ownerUser);

    expect($ownerUser->can('view', $ownUnit))->toBeTrue();
    expect($ownerUser->can('view', $otherUnit))->toBeFalse();
    expect($ownerUser->can('update', $ownUnit))->toBeFalse();
});

test('a tenant cannot manage units', function () {
    $organization = Organization::factory()->create();
    $tenant = memberOf($organization, OrganizationRole::Tenant);
    $unit = Unit::factory()->for($organization)->create();

    expect($tenant->can('view', $unit))->toBeFalse();
    expect($tenant->can('create', Unit::class))->toBeFalse();
});

test('a property manager from another organization cannot access the unit', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $unitA = Unit::factory()->for($organizationA)->create();

    expect($managerB->can('view', $unitA))->toBeFalse();
    expect($managerB->can('update', $unitA))->toBeFalse();
});

test('a super admin can manage any unit regardless of organization', function () {
    $organization = Organization::factory()->create();
    $unit = Unit::factory()->for($organization)->create();

    $superAdmin = User::factory()->create(['is_super_admin' => true]);

    expect($superAdmin->can('view', $unit))->toBeTrue();
    expect($superAdmin->can('update', $unit))->toBeTrue();
    expect($superAdmin->can('delete', $unit))->toBeTrue();
});
