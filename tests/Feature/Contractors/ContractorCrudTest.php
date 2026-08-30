<?php

use App\Enums\OrganizationRole;
use App\Models\Contractor;
use App\Models\Organization;

test('a property manager can view the contractors list scoped to their organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $ownContractor = Contractor::factory()->for($organization)->create();
    $otherContractor = Contractor::factory()->create();

    $this->actingAs($manager)
        ->get(route('contractors.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Contractors/Index')
            ->has('contractors.data', 1)
            ->where('contractors.data.0.company_name', $ownContractor->company_name)
        );

    expect($otherContractor->company_name)->not->toBe($ownContractor->company_name);
});

test('a property manager can create a contractor', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $this->actingAs($manager)
        ->post(route('contractors.store'), [
            'company_name' => 'Sanitär Müller GmbH',
            'contact_name' => 'Peter Müller',
            'email' => 'kontakt@sanitaer-mueller.test',
            'phone' => '+49 30 1234567',
            'specialty' => 'Sanitär',
        ])
        ->assertRedirect(route('contractors.index'));

    $this->assertDatabaseHas('contractors', [
        'organization_id' => $organization->id,
        'company_name' => 'Sanitär Müller GmbH',
        'specialty' => 'Sanitär',
    ]);
});

test('creating a contractor is rejected with an invalid email', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);

    $this->actingAs($manager)
        ->post(route('contractors.store'), [
            'company_name' => 'Testfirma',
            'email' => 'not-an-email',
        ])
        ->assertSessionHasErrors('email');

    $this->assertDatabaseMissing('contractors', ['company_name' => 'Testfirma']);
});

test('a property manager can update a contractor', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $contractor = Contractor::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->put(route('contractors.update', $contractor), [
            'company_name' => 'Neuer Name GmbH',
            'email' => $contractor->email,
            'phone' => $contractor->phone,
            'specialty' => $contractor->specialty,
        ])
        ->assertRedirect(route('contractors.index'));

    expect($contractor->fresh()->company_name)->toBe('Neuer Name GmbH');
});

test('a property manager can delete a contractor', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $contractor = Contractor::factory()->for($organization)->create();

    $this->actingAs($manager)
        ->delete(route('contractors.destroy', $contractor))
        ->assertRedirect(route('contractors.index'));

    expect($contractor->fresh()->trashed())->toBeTrue();
});

test('a tenant cannot create, update or delete contractors', function () {
    $organization = Organization::factory()->create();
    $tenant = memberOf($organization, OrganizationRole::Tenant);
    $contractor = Contractor::factory()->for($organization)->create();

    $this->actingAs($tenant)->get(route('contractors.create'))->assertForbidden();

    $this->actingAs($tenant)
        ->post(route('contractors.store'), ['company_name' => 'Testfirma'])
        ->assertForbidden();

    $this->actingAs($tenant)
        ->put(route('contractors.update', $contractor), ['company_name' => 'Anders'])
        ->assertForbidden();

    $this->actingAs($tenant)
        ->delete(route('contractors.destroy', $contractor))
        ->assertForbidden();
});

test('a property manager cannot update a contractor belonging to another organization', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $contractorA = Contractor::factory()->for($organizationA)->create();

    $this->actingAs($managerB)
        ->put(route('contractors.update', $contractorA), ['company_name' => 'Übernommen'])
        ->assertNotFound();
});
