<?php

use App\Enums\OrganizationRole;
use App\Enums\UnitStatus;
use App\Enums\UnitType;
use App\Models\Building;
use App\Models\Organization;
use App\Models\Unit;

test('a property manager can view the units list scoped to their organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $ownUnit = Unit::factory()->for($organization)->create();
    $otherUnit = Unit::factory()->create();

    $this->actingAs($manager)
        ->get(route('units.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Units/Index')
            ->has('units.data', 1)
            ->where('units.data.0.unit_number', $ownUnit->unit_number)
        );

    expect($otherUnit->unit_number)->not->toBe($ownUnit->unit_number);
});

test('a property manager can create a unit', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $building = Building::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->post(route('units.store'), [
            'building_id' => $building->id,
            'type' => UnitType::Commercial->value,
            'unit_number' => '12',
            'floor' => 3,
            'size_sqm' => 65.5,
            'rooms' => 3,
            'status' => UnitStatus::Vacant->value,
        ])
        ->assertRedirect(route('units.index'));

    $this->assertDatabaseHas('units', [
        'organization_id' => $organization->id,
        'building_id' => $building->id,
        'unit_number' => '12',
        'type' => 'commercial',
        'status' => 'vacant',
    ]);
});

test('creating a unit is rejected without a valid type', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $building = Building::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->post(route('units.store'), [
            'building_id' => $building->id,
            'unit_number' => '1',
            'status' => UnitStatus::Vacant->value,
        ])
        ->assertSessionHasErrors('type');

    $this->actingAs($manager)
        ->post(route('units.store'), [
            'building_id' => $building->id,
            'type' => 'penthouse',
            'unit_number' => '1',
            'status' => UnitStatus::Vacant->value,
        ])
        ->assertSessionHasErrors('type');
});

test('creating a unit is rejected if the building belongs to another organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $otherOrganization = Organization::factory()->create();
    $foreignBuilding = Building::factory()->for($otherOrganization)->create();

    $this->actingAs($manager)
        ->post(route('units.store'), [
            'building_id' => $foreignBuilding->id,
            'type' => UnitType::Apartment->value,
            'unit_number' => '1',
            'status' => UnitStatus::Vacant->value,
        ])
        ->assertSessionHasErrors('building_id');

    $this->assertDatabaseMissing('units', ['unit_number' => '1', 'organization_id' => $organization->id]);
});

test('creating a unit is rejected when the unit number already exists in the same building', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $building = Building::factory()->for($organization)->create();
    Unit::factory()->for($organization)->for($building)->create(['unit_number' => '5']);

    $this->actingAs($manager)
        ->post(route('units.store'), [
            'building_id' => $building->id,
            'type' => UnitType::Apartment->value,
            'unit_number' => '5',
            'status' => UnitStatus::Vacant->value,
        ])
        ->assertSessionHasErrors('unit_number');
});

test('a property manager can update a unit', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $building = Building::factory()->for($organization)->create();
    $unit = Unit::factory()->for($organization)->for($building)->create();

    $this->actingAs($manager)
        ->put(route('units.update', $unit), [
            'building_id' => $building->id,
            'type' => UnitType::ParkingSpace->value,
            'unit_number' => $unit->unit_number,
            'status' => UnitStatus::Occupied->value,
        ])
        ->assertRedirect(route('units.index'));

    expect($unit->fresh()->status)->toBe(UnitStatus::Occupied);
    expect($unit->fresh()->type)->toBe(UnitType::ParkingSpace);
});

test('a property manager can delete a unit', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $unit = Unit::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->delete(route('units.destroy', $unit))
        ->assertRedirect(route('units.index'));

    expect($unit->fresh()->trashed())->toBeTrue();
});

test('a tenant cannot create, update or delete units', function () {
    $organization = Organization::factory()->create();
    $tenant = memberOf($organization, OrganizationRole::Tenant);
    $unit = Unit::factory()->for($organization)->create();

    $this->actingAs($tenant)->get(route('units.create'))->assertForbidden();

    $this->actingAs($tenant)
        ->post(route('units.store'), ['unit_number' => '1', 'status' => UnitStatus::Vacant->value])
        ->assertForbidden();

    $this->actingAs($tenant)
        ->put(route('units.update', $unit), ['unit_number' => '1', 'status' => UnitStatus::Vacant->value])
        ->assertForbidden();

    $this->actingAs($tenant)
        ->delete(route('units.destroy', $unit))
        ->assertForbidden();
});

test('the units list can be filtered by status', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $building = Building::factory()->for($organization)->create();

    $vacant = Unit::factory()->for($organization)->for($building)->create(['status' => UnitStatus::Vacant]);
    Unit::factory()->for($organization)->for($building)->create(['status' => UnitStatus::Occupied]);

    $this->actingAs($manager)
        ->get(route('units.index', ['status' => 'vacant']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Units/Index')
            ->has('units.data', 1)
            ->where('units.data.0.unit_number', $vacant->unit_number)
            ->where('filters.status', 'vacant')
        );
});

test('the units list can be searched by unit number', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $building = Building::factory()->for($organization)->create();

    $match = Unit::factory()->for($organization)->for($building)->create(['unit_number' => '101']);
    $noMatch = Unit::factory()->for($organization)->for($building)->create(['unit_number' => '202']);

    $this->actingAs($manager)
        ->get(route('units.index', ['search' => '101']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Units/Index')
            ->has('units.data', 1)
            ->where('units.data.0.unit_number', $match->unit_number)
        );

    expect($noMatch->unit_number)->not->toBe($match->unit_number);
});

test('a property manager cannot update a unit belonging to another organization', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $unitA = Unit::factory()->for($organizationA)->create();

    $this->actingAs($managerB)
        ->put(route('units.update', $unitA), ['unit_number' => 'X', 'status' => UnitStatus::Vacant->value])
        ->assertNotFound();
});
