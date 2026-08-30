<?php

use App\Enums\OrganizationRole;
use App\Models\Contractor;
use App\Models\Organization;
use App\Models\User;

test('a property manager can view, update and delete contractors in their organization', function () {
    $organization = Organization::factory()->create();
    $manager = memberOf($organization, OrganizationRole::PropertyManager);
    $contractor = Contractor::factory()->for($organization)->create();

    expect($manager->can('view', $contractor))->toBeTrue();
    expect($manager->can('update', $contractor))->toBeTrue();
    expect($manager->can('delete', $contractor))->toBeTrue();
    expect($manager->can('create', Contractor::class))->toBeTrue();
});

test('a contractor can only view their own contractor record, not manage it', function () {
    $organization = Organization::factory()->create();
    $contractorUser = memberOf($organization, OrganizationRole::Contractor);

    $ownRecord = Contractor::factory()->for($organization)->create(['user_id' => $contractorUser->id]);
    $otherRecord = Contractor::factory()->for($organization)->create();

    expect($contractorUser->can('view', $ownRecord))->toBeTrue();
    expect($contractorUser->can('view', $otherRecord))->toBeFalse();
    expect($contractorUser->can('update', $ownRecord))->toBeFalse();
});

test('a tenant cannot manage contractors', function () {
    $organization = Organization::factory()->create();
    $tenant = memberOf($organization, OrganizationRole::Tenant);
    $contractor = Contractor::factory()->for($organization)->create();

    expect($tenant->can('view', $contractor))->toBeFalse();
    expect($tenant->can('create', Contractor::class))->toBeFalse();
});

test('a property manager from another organization cannot access the contractor', function () {
    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $managerB = memberOf($organizationB, OrganizationRole::PropertyManager);
    $contractorA = Contractor::factory()->for($organizationA)->create();

    expect($managerB->can('view', $contractorA))->toBeFalse();
    expect($managerB->can('update', $contractorA))->toBeFalse();
});

test('a super admin can manage any contractor regardless of organization', function () {
    $organization = Organization::factory()->create();
    $contractor = Contractor::factory()->for($organization)->create();

    $superAdmin = User::factory()->create(['is_super_admin' => true]);

    expect($superAdmin->can('view', $contractor))->toBeTrue();
    expect($superAdmin->can('update', $contractor))->toBeTrue();
    expect($superAdmin->can('delete', $contractor))->toBeTrue();
});
