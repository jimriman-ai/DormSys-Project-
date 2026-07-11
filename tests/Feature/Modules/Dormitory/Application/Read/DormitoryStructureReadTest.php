<?php

declare(strict_types=1);

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadContract;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BuildingModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

function seedStructureReadDormitory(string $code, string $name = 'Read Dormitory'): DormitoryModel
{
    return DormitoryModel::query()->create([
        'code' => $code,
        'name' => $name,
        'status' => ResourceStatus::Available,
    ]);
}

function seedStructureReadBuilding(DormitoryModel $dormitory, string $code): BuildingModel
{
    return BuildingModel::query()->create([
        'dormitory_id' => $dormitory->getId(),
        'code' => $code,
        'name' => 'Building '.$code,
        'status' => ResourceStatus::Available,
    ]);
}

it('lists dormitories', function (): void {
    seedStructureReadDormitory('READ-A', 'Alpha');
    seedStructureReadDormitory('READ-B', 'Beta');

    $result = app(DormitoryStructureReadContract::class)->listDormitories();

    expect($result)->toHaveCount(2)
        ->and($result[0]->code)->toBe('READ-A')
        ->and($result[0]->name)->toBe('Alpha')
        ->and($result[0]->status)->toBe(ResourceStatus::Available->value)
        ->and($result[1]->code)->toBe('READ-B');
});

it('returns an empty list when no dormitories exist', function (): void {
    expect(app(DormitoryStructureReadContract::class)->listDormitories())->toBe([]);
});

it('retrieves one dormitory detail', function (): void {
    $dormitory = seedStructureReadDormitory('READ-DETAIL', 'Detail Site');

    $detail = app(DormitoryStructureReadContract::class)->getDormitoryDetail($dormitory->getId());

    expect($detail)->not->toBeNull()
        ->and($detail?->id)->toBe($dormitory->getId())
        ->and($detail?->code)->toBe('READ-DETAIL')
        ->and($detail?->name)->toBe('Detail Site')
        ->and($detail?->status)->toBe(ResourceStatus::Available->value);
});

it('returns null for missing dormitory detail', function (): void {
    $missing = app(DormitoryStructureReadContract::class)
        ->getDormitoryDetail(Uuid::uuid7()->toString());

    expect($missing)->toBeNull();
});

it('lists buildings for a dormitory', function (): void {
    $dormitory = seedStructureReadDormitory('READ-BLDG');
    seedStructureReadBuilding($dormitory, 'B');
    seedStructureReadBuilding($dormitory, 'A');
    seedStructureReadBuilding(seedStructureReadDormitory('READ-OTHER'), 'Z');

    $buildings = app(DormitoryStructureReadContract::class)
        ->listDormitoryBuildings($dormitory->getId());

    expect($buildings)->toHaveCount(2)
        ->and($buildings[0]->code)->toBe('A')
        ->and($buildings[1]->code)->toBe('B')
        ->and($buildings[0]->dormitoryId)->toBe($dormitory->getId());
});

it('returns an empty building list for a missing dormitory', function (): void {
    expect(app(DormitoryStructureReadContract::class)
        ->listDormitoryBuildings(Uuid::uuid7()->toString()))->toBe([]);
});

it('does not write when reading', function (): void {
    $dormitory = seedStructureReadDormitory('READ-RO');
    seedStructureReadBuilding($dormitory, 'A');

    $beforeDormitories = DormitoryModel::query()->count();
    $beforeBuildings = BuildingModel::query()->count();
    $queries = 0;

    DB::listen(function ($query) use (&$queries): void {
        if (preg_match('/^\s*(insert|update|delete)\b/i', $query->sql) === 1) {
            $queries++;
        }
    });

    $reads = app(DormitoryStructureReadContract::class);
    $reads->listDormitories();
    $reads->getDormitoryDetail($dormitory->getId());
    $reads->listDormitoryBuildings($dormitory->getId());

    expect($queries)->toBe(0)
        ->and(DormitoryModel::query()->count())->toBe($beforeDormitories)
        ->and(BuildingModel::query()->count())->toBe($beforeBuildings);
});
