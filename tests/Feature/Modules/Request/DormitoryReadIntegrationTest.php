<?php

declare(strict_types=1);

use App\Integrations\Request\DormitoryReadBridge;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryAssignment;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Request\Application\Contracts\DormitoryReadContract;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

it('resolves the live dormitory read bridge for request', function (): void {
    expect(app(DormitoryReadContract::class)::class)->toBe(DormitoryReadBridge::class);
});

it('returns true when dormitory detail exists', function (): void {
    $dormitory = DormitoryModel::query()->create([
        'code' => 'REQ-INT-EXISTS',
        'name' => 'Request Integration Site',
        'status' => ResourceStatus::Available,
    ]);

    // Intent: live bridge boolean when detail exists (authorized structure viewer).
    $exists = withDormitoryStructureViewActor(
        fn (): bool => app(DormitoryReadContract::class)->siteExists($dormitory->getId()),
    );

    expect($exists)->toBeTrue();
});

it('returns false when dormitory detail is missing', function (): void {
    // Intent: live bridge boolean when detail is absent (authorized structure viewer).
    $exists = withDormitoryStructureViewActor(
        fn (): bool => app(DormitoryReadContract::class)->siteExists(Uuid::uuid7()->toString()),
    );

    expect($exists)->toBeFalse();
});

it('listSites returns an empty list when no dormitories exist', function (): void {
    $sites = withDormitoryStructureViewActor(
        fn (): array => app(DormitoryReadContract::class)->listSites(),
    );

    expect($sites)->toBeEmpty();
});

it('listSites returns dormitory site summaries ordered by code', function (): void {
    DormitoryModel::query()->create([
        'code' => 'REQ-SITE-B',
        'name' => 'Site Bravo',
        'status' => ResourceStatus::Available,
    ]);
    DormitoryModel::query()->create([
        'code' => 'REQ-SITE-A',
        'name' => 'Site Alpha',
        'status' => ResourceStatus::Unavailable,
    ]);
    DormitoryModel::query()->create([
        'code' => 'REQ-SITE-C',
        'name' => 'Site Charlie',
        'status' => ResourceStatus::Available,
    ]);

    $sites = withDormitoryStructureViewActor(
        fn (): array => app(DormitoryReadContract::class)->listSites(),
    );

    expect($sites)->toHaveCount(3)
        ->and($sites[0]->code)->toBe('REQ-SITE-A')
        ->and($sites[0]->name)->toBe('Site Alpha')
        ->and($sites[0]->status)->toBe(ResourceStatus::Unavailable->value)
        ->and($sites[1]->code)->toBe('REQ-SITE-B')
        ->and($sites[2]->code)->toBe('REQ-SITE-C')
        ->and($sites[0]->id)->not->toBeEmpty()
        ->and($sites[1]->id)->not->toBeEmpty()
        ->and($sites[2]->id)->not->toBeEmpty();
});

it('listAssignedSitesForUser returns only actively assigned dormitories', function (): void {
    $userId = Uuid::uuid7()->toString();
    DB::table('identity_users')->insert([
        'id' => $userId,
        'status' => UserStatus::Active->value,
        'display_name' => 'Assigned Sites User',
        'email' => 'assigned.sites.'.uniqid('', true).'@dormsys.local',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $mine = DormitoryModel::query()->create([
        'code' => 'REQ-ASN-A',
        'name' => 'Assigned Alpha',
        'status' => ResourceStatus::Available,
    ]);
    DormitoryModel::query()->create([
        'code' => 'REQ-ASN-B',
        'name' => 'Unassigned Bravo',
        'status' => ResourceStatus::Available,
    ]);

    DormitoryAssignment::query()->create([
        'user_id' => $userId,
        'dormitory_id' => $mine->getId(),
        'assigned_at' => now(),
        'revoked_at' => null,
    ]);

    $sites = app(DormitoryReadContract::class)->listAssignedSitesForUser($userId);

    expect($sites)->toHaveCount(1)
        ->and($sites[0]->id)->toBe($mine->getId())
        ->and($sites[0]->code)->toBe('REQ-ASN-A')
        ->and($sites[0]->name)->toBe('Assigned Alpha')
        ->and($sites[0]->status)->toBe(ResourceStatus::Available->value);
});

it('listAssignedSitesForUser excludes revoked assignments and returns empty for unassigned users', function (): void {
    $assignedThenRevoked = Uuid::uuid7()->toString();
    $neverAssigned = Uuid::uuid7()->toString();

    foreach ([$assignedThenRevoked, $neverAssigned] as $userId) {
        DB::table('identity_users')->insert([
            'id' => $userId,
            'status' => UserStatus::Active->value,
            'display_name' => 'Sites User '.$userId,
            'email' => 'sites.'.uniqid('', true).'@dormsys.local',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $dormitory = DormitoryModel::query()->create([
        'code' => 'REQ-ASN-REV',
        'name' => 'Revoked Site',
        'status' => ResourceStatus::Available,
    ]);

    DormitoryAssignment::query()->create([
        'user_id' => $assignedThenRevoked,
        'dormitory_id' => $dormitory->getId(),
        'assigned_at' => now(),
        'revoked_at' => now(),
    ]);

    $contract = app(DormitoryReadContract::class);

    expect($contract->listAssignedSitesForUser($assignedThenRevoked))->toBeEmpty()
        ->and($contract->listAssignedSitesForUser($neverAssigned))->toBeEmpty();
});
