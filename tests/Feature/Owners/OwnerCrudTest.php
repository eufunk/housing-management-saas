<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Owner;

test('a property manager can view the owners list scoped to their organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $ownOwner = Owner::factory()->for($organization)->create();
    $otherOwner = Owner::factory()->create();

    $this->actingAs($manager)
        ->get(route('owners.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Owners/Index')
            ->has('owners.data', 1)
            ->where('owners.data.0.name', $ownOwner->name)
        );

    expect($otherOwner->name)->not->toBe($ownOwner->name);
});

test('a property manager can create an owner', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $this->actingAs($manager)
        ->post(route('owners.store'), [
            'name' => 'Sabine Hoffmann',
            'email' => 'sabine@example.test',
            'phone' => '+49 30 1234567',
        ])
        ->assertRedirect(route('owners.index'));

    $this->assertDatabaseHas('owners', [
        'organization_id' => $organization->id,
        'name' => 'Sabine Hoffmann',
        'email' => 'sabine@example.test',
    ]);
});

test('creating an owner is rejected with an invalid email', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $this->actingAs($manager)
        ->post(route('owners.store'), [
            'name' => 'Testperson',
            'email' => 'not-an-email',
        ])
        ->assertSessionHasErrors('email');

    $this->assertDatabaseMissing('owners', ['name' => 'Testperson']);
});

test('a property manager can update an owner', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $owner = Owner::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->put(route('owners.update', $owner), [
            'name' => 'Neuer Name',
            'email' => $owner->email,
            'phone' => $owner->phone,
        ])
        ->assertRedirect(route('owners.index'));

    expect($owner->fresh()->name)->toBe('Neuer Name');
});

test('a property manager can delete an owner', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $owner = Owner::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->delete(route('owners.destroy', $owner))
        ->assertRedirect(route('owners.index'));

    expect($owner->fresh()->trashed())->toBeTrue();
});

test('a tenant cannot create, update or delete owners', function () {
    $organization = Organization::factory()->create();
    $tenant = memberOf($organization, OrganizationRole::Tenant);
    $owner = Owner::factory()->for($organization)->create();

    $this->actingAs($tenant)->get(route('owners.create'))->assertForbidden();

    $this->actingAs($tenant)
        ->post(route('owners.store'), ['name' => 'Testperson'])
        ->assertForbidden();

    $this->actingAs($tenant)
        ->put(route('owners.update', $owner), ['name' => 'Anders'])
        ->assertForbidden();

    $this->actingAs($tenant)
        ->delete(route('owners.destroy', $owner))
        ->assertForbidden();
});

test('a property manager cannot update an owner belonging to another organization', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $ownerA = Owner::factory()->for($organizationA)->create();

    $this->actingAs($managerB)
        ->put(route('owners.update', $ownerA), ['name' => 'Übernommen'])
        ->assertNotFound();
});
