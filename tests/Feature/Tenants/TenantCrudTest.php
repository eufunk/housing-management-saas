<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Tenant;

test('a property manager can view the tenants list scoped to their organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $ownTenant = Tenant::factory()->for($organization)->create();
    $otherTenant = Tenant::factory()->create();

    $this->actingAs($manager)
        ->get(route('tenants.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Tenants/Index')
            ->has('tenants.data', 1)
            ->where('tenants.data.0.name', $ownTenant->name)
        );

    expect($otherTenant->name)->not->toBe($ownTenant->name);
});

test('a property manager can create a tenant', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $this->actingAs($manager)
        ->post(route('tenants.store'), [
            'name' => 'Julia Sommerfeld',
            'email' => 'julia@example.test',
            'phone' => '+49 89 1234567',
        ])
        ->assertRedirect(route('tenants.index'));

    $this->assertDatabaseHas('tenants', [
        'organization_id' => $organization->id,
        'name' => 'Julia Sommerfeld',
        'email' => 'julia@example.test',
    ]);
});

test('creating a tenant is rejected with an invalid email', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $this->actingAs($manager)
        ->post(route('tenants.store'), [
            'name' => 'Testperson',
            'email' => 'not-an-email',
        ])
        ->assertSessionHasErrors('email');

    $this->assertDatabaseMissing('tenants', ['name' => 'Testperson']);
});

test('a property manager can update a tenant', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $tenant = Tenant::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->put(route('tenants.update', $tenant), [
            'name' => 'Neuer Name',
            'email' => $tenant->email,
            'phone' => $tenant->phone,
        ])
        ->assertRedirect(route('tenants.index'));

    expect($tenant->fresh()->name)->toBe('Neuer Name');
});

test('a property manager can delete a tenant', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $tenant = Tenant::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->delete(route('tenants.destroy', $tenant))
        ->assertRedirect(route('tenants.index'));

    expect($tenant->fresh()->trashed())->toBeTrue();
});

test('an owner cannot create, update or delete tenants', function () {
    $organization = Organization::factory()->create();
    $ownerUser = memberOf($organization, OrganizationRole::Owner);
    $tenant = Tenant::factory()->for($organization)->create();

    $this->actingAs($ownerUser)->get(route('tenants.create'))->assertForbidden();

    $this->actingAs($ownerUser)
        ->post(route('tenants.store'), ['name' => 'Testperson'])
        ->assertForbidden();

    $this->actingAs($ownerUser)
        ->put(route('tenants.update', $tenant), ['name' => 'Anders'])
        ->assertForbidden();

    $this->actingAs($ownerUser)
        ->delete(route('tenants.destroy', $tenant))
        ->assertForbidden();
});

test('a property manager cannot update a tenant belonging to another organization', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $tenantA = Tenant::factory()->for($organizationA)->create();

    $this->actingAs($managerB)
        ->put(route('tenants.update', $tenantA), ['name' => 'Übernommen'])
        ->assertNotFound();
});
