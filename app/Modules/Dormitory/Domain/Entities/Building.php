<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Domain\Entities;

use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Domain\Exceptions\InvalidDormitoryHierarchy;
use App\Modules\Dormitory\Domain\ValueObjects\BuildingId;
use App\Modules\Dormitory\Domain\ValueObjects\DormitoryId;

/**
 * Physical building inside a dormitory. Groups floors.
 */
final class Building
{
    /** @var list<Floor> */
    private array $floors = [];

    public function __construct(
        public readonly BuildingId $id,
        public readonly DormitoryId $dormitoryId,
        public string $code,
        public string $name,
        public ResourceStatus $status = ResourceStatus::Available,
    ) {}

    public static function create(
        BuildingId $id,
        DormitoryId $dormitoryId,
        string $code,
        string $name,
        ResourceStatus $status = ResourceStatus::Available,
    ): self {
        return new self(
            id: $id,
            dormitoryId: $dormitoryId,
            code: $code,
            name: $name,
            status: $status,
        );
    }

    public function belongsToDormitory(DormitoryId $dormitoryId): bool
    {
        return $this->dormitoryId->equals($dormitoryId);
    }

    public function addFloor(Floor $floor): void
    {
        if (! $floor->belongsToBuilding($this->id)) {
            throw new InvalidDormitoryHierarchy('Floor does not belong to this building.');
        }

        foreach ($this->floors as $existing) {
            if ($existing->id->equals($floor->id)) {
                throw new InvalidDormitoryHierarchy('Floor is already registered in this building.');
            }
        }

        $this->floors[] = $floor;
    }

    /**
     * @return list<Floor>
     */
    public function floors(): array
    {
        return $this->floors;
    }
}
