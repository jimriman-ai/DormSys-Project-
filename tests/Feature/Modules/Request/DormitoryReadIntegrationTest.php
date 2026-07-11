<?php

declare(strict_types=1);

use App\Integrations\Request\DormitoryReadBridge;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Request\Application\Contracts\DormitoryReadContract;
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

    expect(app(DormitoryReadContract::class)->siteExists($dormitory->getId()))->toBeTrue();
});

it('returns false when dormitory detail is missing', function (): void {
    expect(app(DormitoryReadContract::class)->siteExists(Uuid::uuid7()->toString()))->toBeFalse();
});
