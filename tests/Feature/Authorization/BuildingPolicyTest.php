<?php

use App\Enums\OrganizationRole;
use App\Models\Building;
use App\Models\Organization;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;

test('a property manager can view, update and delete buildings in their organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $building = Building::factory()->for($organization)->create();

    expect($manager->can('view', $building))->toBeTrue();
    expect($manager->can('update', $building))->toBeTrue();
    expect($manager->can('delete', $building))->toBeTrue();
    expect($manager->can('create', Building::class))->toBeTrue();
});

test('an owner can only view buildings of their own property, not manage them', function () {
    $organization = Organization::factory()->create();
    $ownerUser = memberOf($organization, OrganizationRole::Owner);

    $owner = Owner::factory()->for($organization)->create(['user_id' => $ownerUser->id]);
    $ownProperty = Property::factory()->for($organization)->create(['owner_id' => $owner->id]);
    $ownBuilding = Building::factory()->for($organization)->for($ownProperty)->create();
    $otherBuilding = Building::factory()->for($organization)->create();

    // BuildingPolicy::view() loads $building->property->owner, which — like
    // every organization-scoped relation — is filtered by OrganizationScope
    // based on the authenticated user, so it must actually be resolvable here.
    $this->actingAs($ownerUser);

    expect($ownerUser->can('view', $ownBuilding))->toBeTrue();
    expect($ownerUser->can('view', $otherBuilding))->toBeFalse();
    expect($ownerUser->can('update', $ownBuilding))->toBeFalse();
});

test('a tenant cannot manage buildings', function () {
    $organization = Organization::factory()->create();
    $tenant = memberOf($organization, OrganizationRole::Tenant);
    $building = Building::factory()->for($organization)->create();

    expect($tenant->can('view', $building))->toBeFalse();
    expect($tenant->can('create', Building::class))->toBeFalse();
});

test('a property manager from another organization cannot access the building', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $buildingA = Building::factory()->for($organizationA)->create();

    expect($managerB->can('view', $buildingA))->toBeFalse();
    expect($managerB->can('update', $buildingA))->toBeFalse();
});

test('a super admin can manage any building regardless of organization', function () {
    $organization = Organization::factory()->create();
    $building = Building::factory()->for($organization)->create();

    $superAdmin = User::factory()->create(['is_super_admin' => true]);

    expect($superAdmin->can('view', $building))->toBeTrue();
    expect($superAdmin->can('update', $building))->toBeTrue();
    expect($superAdmin->can('delete', $building))->toBeTrue();
});
