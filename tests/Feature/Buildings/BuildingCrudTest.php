<?php

use App\Enums\OrganizationRole;
use App\Models\Building;
use App\Models\Organization;
use App\Models\Property;

test('a property manager can view the buildings list scoped to their organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $ownBuilding = Building::factory()->for($organization)->create();
    $otherBuilding = Building::factory()->create();

    $this->actingAs($manager)
        ->get(route('buildings.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Buildings/Index')
            ->has('buildings.data', 1)
            ->where('buildings.data.0.name', $ownBuilding->name)
        );

    expect($otherBuilding->name)->not->toBe($ownBuilding->name);
});

test('a property manager can create a building', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $property = Property::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->post(route('buildings.store'), [
            'property_id' => $property->id,
            'name' => 'Haus A',
            'floors' => 4,
        ])
        ->assertRedirect(route('buildings.index'));

    $this->assertDatabaseHas('buildings', [
        'organization_id' => $organization->id,
        'property_id' => $property->id,
        'name' => 'Haus A',
        'floors' => 4,
    ]);
});

test('creating a building is rejected if the property belongs to another organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $otherOrganization = Organization::factory()->create();
    $foreignProperty = Property::factory()->for($otherOrganization)->create();

    $this->actingAs($manager)
        ->post(route('buildings.store'), [
            'property_id' => $foreignProperty->id,
            'name' => 'Testgebäude',
        ])
        ->assertSessionHasErrors('property_id');

    $this->assertDatabaseMissing('buildings', ['name' => 'Testgebäude']);
});

test('a property manager can update a building', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $property = Property::factory()->for($organization)->create();
    $building = Building::factory()->for($organization)->for($property)->create();

    $this->actingAs($manager)
        ->put(route('buildings.update', $building), [
            'property_id' => $property->id,
            'name' => 'Neuer Name',
            'floors' => $building->floors,
        ])
        ->assertRedirect(route('buildings.index'));

    expect($building->fresh()->name)->toBe('Neuer Name');
});

test('a property manager can delete a building', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $building = Building::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->delete(route('buildings.destroy', $building))
        ->assertRedirect(route('buildings.index'));

    expect($building->fresh()->trashed())->toBeTrue();
});

test('a tenant cannot create, update or delete buildings', function () {
    $organization = Organization::factory()->create();
    $tenant = memberOf($organization, OrganizationRole::Tenant);
    $building = Building::factory()->for($organization)->create();

    $this->actingAs($tenant)->get(route('buildings.create'))->assertForbidden();

    $this->actingAs($tenant)
        ->post(route('buildings.store'), ['name' => 'Testgebäude'])
        ->assertForbidden();

    $this->actingAs($tenant)
        ->put(route('buildings.update', $building), ['name' => 'Anders'])
        ->assertForbidden();

    $this->actingAs($tenant)
        ->delete(route('buildings.destroy', $building))
        ->assertForbidden();
});

test('a property manager cannot update a building belonging to another organization', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $buildingA = Building::factory()->for($organizationA)->create();

    $this->actingAs($managerB)
        ->put(route('buildings.update', $buildingA), ['name' => 'Übernommen'])
        ->assertNotFound();
});
