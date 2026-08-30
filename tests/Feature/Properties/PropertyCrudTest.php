<?php

use App\Enums\OrganizationRole;
use App\Models\Building;
use App\Models\Organization;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Unit;

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

test('a property manager can view a property detail page with its buildings and units', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $property = Property::factory()->for($organization)->create();
    $building = Building::factory()->for($organization)->for($property)->create();
    $unit = Unit::factory()->for($organization)->for($building)->create();

    $this->actingAs($manager)
        ->get(route('properties.show', $property))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Properties/Show')
            ->where('property.name', $property->name)
            ->has('property.buildings', 1)
            ->where('property.buildings.0.name', $building->name)
            ->has('property.buildings.0.units', 1)
            ->where('property.buildings.0.units.0.unit_number', $unit->unit_number)
        );
});

test('a property manager from another organization gets a 404 for a property detail page', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $propertyA = Property::factory()->for($organizationA)->create();

    $this->actingAs($managerB)
        ->get(route('properties.show', $propertyA))
        ->assertNotFound();
});

test('the properties list can be searched by name and city', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $match = Property::factory()->for($organization)->create(['name' => 'Wohnanlage Sonnenhof', 'city' => 'Berlin']);
    $alsoMatch = Property::factory()->for($organization)->create(['name' => 'Anderes Haus', 'city' => 'Sonnenhofstadt']);
    $noMatch = Property::factory()->for($organization)->create(['name' => 'Villa Nord', 'city' => 'Hamburg']);

    $this->actingAs($manager)
        ->get(route('properties.index', ['search' => 'Sonnenhof']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Properties/Index')
            ->has('properties.data', 2)
            ->where('filters.search', 'Sonnenhof')
        );

    expect($noMatch->name)->not->toContain('Sonnenhof');
    expect($match->name)->toContain('Sonnenhof');
    expect($alsoMatch->city)->toContain('Sonnenhof');
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
