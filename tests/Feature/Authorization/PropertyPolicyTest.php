<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;

test('a property manager can view, update and delete properties in their organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $property = Property::factory()->for($organization)->create();

    expect($manager->can('view', $property))->toBeTrue();
    expect($manager->can('update', $property))->toBeTrue();
    expect($manager->can('delete', $property))->toBeTrue();
    expect($manager->can('create', Property::class))->toBeTrue();
});

test('an owner can only view their own property, not manage it', function () {
    $organization = Organization::factory()->create();
    $ownerUser = memberOf($organization, OrganizationRole::Owner);

    $owner = Owner::factory()->for($organization)->create(['user_id' => $ownerUser->id]);
    $ownProperty = Property::factory()->for($organization)->create(['owner_id' => $owner->id]);
    $otherProperty = Property::factory()->for($organization)->create();

    // PropertyPolicy::view() loads $property->owner, which — like every
    // organization-scoped relation — is filtered by OrganizationScope based
    // on the authenticated user, so it must actually be resolvable here.
    $this->actingAs($ownerUser);

    expect($ownerUser->can('view', $ownProperty))->toBeTrue();
    expect($ownerUser->can('view', $otherProperty))->toBeFalse();
    expect($ownerUser->can('update', $ownProperty))->toBeFalse();
});

test('a tenant cannot manage properties', function () {
    $organization = Organization::factory()->create();
    $tenant = memberOf($organization, OrganizationRole::Tenant);
    $property = Property::factory()->for($organization)->create();

    expect($tenant->can('view', $property))->toBeFalse();
    expect($tenant->can('create', Property::class))->toBeFalse();
});

test('a property manager from another organization cannot access the property', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $propertyA = Property::factory()->for($organizationA)->create();

    expect($managerB->can('view', $propertyA))->toBeFalse();
    expect($managerB->can('update', $propertyA))->toBeFalse();
});

test('a super admin can manage any property regardless of organization', function () {
    $organization = Organization::factory()->create();
    $property = Property::factory()->for($organization)->create();

    $superAdmin = User::factory()->create(['is_super_admin' => true]);

    expect($superAdmin->can('view', $property))->toBeTrue();
    expect($superAdmin->can('update', $property))->toBeTrue();
    expect($superAdmin->can('delete', $property))->toBeTrue();
});
