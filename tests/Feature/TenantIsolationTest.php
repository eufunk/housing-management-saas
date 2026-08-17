<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Property;
use App\Models\User;

test('a user can only see properties belonging to their current organization', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $propertyA = Property::factory()->for($organizationA)->create();
    $propertyB = Property::factory()->for($organizationB)->create();

    $userA = User::factory()->create(['current_organization_id' => $organizationA->id]);
    $organizationA->users()->attach($userA, ['role' => OrganizationRole::PropertyManager->value]);

    $this->actingAs($userA);

    $visibleProperties = Property::all();

    expect($visibleProperties)->toHaveCount(1);
    expect($visibleProperties->first()->id)->toBe($propertyA->id);
    expect(Property::find($propertyB->id))->toBeNull();
});

test('a user with no current organization sees no organization-scoped records', function () {
    Property::factory()->for(Organization::factory())->create();

    $user = User::factory()->create(['current_organization_id' => null]);
    $this->actingAs($user);

    expect(Property::all())->toHaveCount(0);
});

test('a super admin bypasses tenant scoping', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    Property::factory()->for($organizationA)->create();
    Property::factory()->for($organizationB)->create();

    $superAdmin = User::factory()->create([
        'is_super_admin' => true,
        'current_organization_id' => null,
    ]);
    $this->actingAs($superAdmin);

    expect(Property::all())->toHaveCount(2);
});

test('creating a property without an explicit organization stamps the authenticated user\'s current organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    $organization->users()->attach($user, ['role' => OrganizationRole::PropertyManager->value]);

    $this->actingAs($user);

    $property = Property::create([
        'name' => 'Test Property',
        'street' => 'Test Street 1',
        'postal_code' => '12345',
        'city' => 'Berlin',
        'country' => 'DE',
    ]);

    expect($property->organization_id)->toBe($organization->id);
});
