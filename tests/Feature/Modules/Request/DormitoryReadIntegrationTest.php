<?php

declare(strict_types=1);

use App\Integrations\Request\DormitoryReadBridge;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Request\Application\Contracts\DormitoryReadContract;
use App\Modules\Request\Application\DTOs\DormitorySiteSummaryDTO;
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

    expect($sites)->toBeArray()->toBeEmpty();
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
        ->and($sites[0])->toBeInstanceOf(DormitorySiteSummaryDTO::class)
        ->and($sites[0]->code)->toBe('REQ-SITE-A')
        ->and($sites[0]->name)->toBe('Site Alpha')
        ->and($sites[0]->status)->toBe(ResourceStatus::Unavailable->value)
        ->and($sites[1]->code)->toBe('REQ-SITE-B')
        ->and($sites[2]->code)->toBe('REQ-SITE-C')
        ->and($sites[0]->id)->not->toBeEmpty()
        ->and($sites[1]->id)->not->toBeEmpty()
        ->and($sites[2]->id)->not->toBeEmpty();
});
