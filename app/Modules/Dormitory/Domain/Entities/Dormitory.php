<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Domain\Entities;

use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Domain\Exceptions\InvalidDormitoryHierarchy;
use App\Modules\Dormitory\Domain\ValueObjects\DormitoryId;

/**
 * Aggregate root for accommodation physical resources.
 *
 * Owns the hierarchy Dormitory → Building → Floor → Room → Bed and physical state.
 * Does not own Allocation assignment or CheckIn/CheckOut operational process ownership.
 */
final class Dormitory
{
    /** @var list<Building> */
    private array $buildings = [];

    public function __construct(
        public readonly DormitoryId $id,
        public string $code,
        public string $name,
        public ResourceStatus $status = ResourceStatus::Available,
    ) {}

    public static function create(
        DormitoryId $id,
        string $code,
        string $name,
        ResourceStatus $status = ResourceStatus::Available,
    ): self {
        return new self(
            id: $id,
            code: $code,
            name: $name,
            status: $status,
        );
    }

    public function addBuilding(Building $building): void
    {
        if (! $building->belongsToDormitory($this->id)) {
            throw new InvalidDormitoryHierarchy('Building does not belong to this dormitory.');
        }

        foreach ($this->buildings as $existing) {
            if ($existing->id->equals($building->id)) {
                throw new InvalidDormitoryHierarchy('Building is already registered in this dormitory.');
            }
        }

        $this->buildings[] = $building;
    }

    /**
     * @return list<Building>
     */
    public function buildings(): array
    {
        return $this->buildings;
    }

    public function changeStatus(ResourceStatus $newStatus): void
    {
        $this->status = $newStatus;
    }

    public function isUsable(): bool
    {
        return $this->status->isUsable();
    }
}
