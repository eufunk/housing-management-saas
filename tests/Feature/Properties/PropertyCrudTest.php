<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Owner;
use App\Models\Property;

test('a property manager can view the properties list scoped to their organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $ownProperty = Property::factory()->for($organization)->create();
    $otherProperty = Property::factory()->create();

    $this->actingAs($manager)
        ->get(route('properties.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Properties/Index')
            ->has('properties.data', 1)
            ->where('properties.data.0.name', $ownProperty->name)
        );

    expect($otherProperty->name)->not->toBe($ownProperty->name);
});

test('a property manager can create a property', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $owner = Owner::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->post(route('properties.store'), [
            'owner_id' => $owner->id,
            'name' => 'Mehrfamilienhaus Musterstraße 12',
            'street' => 'Musterstraße 12',
            'postal_code' => '12345',
            'city' => 'Berlin',
            'country' => 'DE',
        ])
        ->assertRedirect(route('properties.index'));

    $this->assertDatabaseHas('properties', [
        'organization_id' => $organization->id,
        'owner_id' => $owner->id,
        'name' => 'Mehrfamilienhaus Musterstraße 12',
    ]);
});

test('creating a property is rejected if the owner belongs to another organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $otherOrganization = Organization::factory()->create();
    $foreignOwner = Owner::factory()->for($otherOrganization)->create();

    $this->actingAs($manager)
        ->post(route('properties.store'), [
            'owner_id' => $foreignOwner->id,
            'name' => 'Testobjekt',
            'street' => 'Teststraße 1',
            'postal_code' => '12345',
            'city' => 'Berlin',
            'country' => 'DE',
        ])
        ->assertSessionHasErrors('owner_id');

    $this->assertDatabaseMissing('properties', ['name' => 'Testobjekt']);
});

test('a property manager can update a property', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $property = Property::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->put(route('properties.update', $property), [
            'owner_id' => null,
            'name' => 'Neuer Name',
            'street' => $property->street,
            'postal_code' => $property->postal_code,
            'city' => $property->city,
            'country' => $property->country,
        ])
        ->assertRedirect(route('properties.index'));

    expect($property->fresh()->name)->toBe('Neuer Name');
});

test('a property manager can delete a property', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $property = Property::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->delete(route('properties.destroy', $property))
        ->assertRedirect(route('properties.index'));

    expect($property->fresh()->trashed())->toBeTrue();
});

test('a tenant cannot create, update or delete properties', function () {
    $organization = Organization::factory()->create();
    $tenant = memberOf($organization, OrganizationRole::Tenant);
    $property = Property::factory()->for($organization)->create();

    $this->actingAs($tenant)->get(route('properties.create'))->assertForbidden();

    $this->actingAs($tenant)
        ->post(route('properties.store'), [
            'name' => 'Testobjekt',
            'street' => 'Teststraße 1',
            'postal_code' => '12345',
            'city' => 'Berlin',
            'country' => 'DE',
        ])
        ->assertForbidden();

    $this->actingAs($tenant)
        ->put(route('properties.update', $property), ['name' => 'Anders'])
        ->assertForbidden();

    $this->actingAs($tenant)
        ->delete(route('properties.destroy', $property))
        ->assertForbidden();
});

test('a property manager cannot update a property belonging to another organization', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $propertyA = Property::factory()->for($organizationA)->create();

    // OrganizationScope hides propertyA from managerB's queries entirely, so
    // route-model binding fails with 404 before the policy is even reached —
    // stronger isolation than a 403, since it doesn't confirm the record exists.
    $this->actingAs($managerB)
        ->put(route('properties.update', $propertyA), ['name' => 'Übernommen'])
        ->assertNotFound();
});
