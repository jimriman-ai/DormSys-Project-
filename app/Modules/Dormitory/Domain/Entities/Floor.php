<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Domain\Entities;

use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Domain\Exceptions\InvalidDormitoryHierarchy;
use App\Modules\Dormitory\Domain\ValueObjects\BuildingId;
use App\Modules\Dormitory\Domain\ValueObjects\FloorId;

/**
 * Physical floor inside a building. Groups rooms.
 */
final class Floor
{
    /** @var list<Room> */
    private array $rooms = [];

    public function __construct(
        public readonly FloorId $id,
        public readonly BuildingId $buildingId,
        public string $label,
        public ResourceStatus $status = ResourceStatus::Available,
    ) {}

    public static function create(
        FloorId $id,
        BuildingId $buildingId,
        string $label,
        ResourceStatus $status = ResourceStatus::Available,
    ): self {
        return new self(
            id: $id,
            buildingId: $buildingId,
            label: $label,
            status: $status,
        );
    }

    public function belongsToBuilding(BuildingId $buildingId): bool
    {
        return $this->buildingId->equals($buildingId);
    }

    public function addRoom(Room $room): void
    {
        if (! $room->belongsToFloor($this->id)) {
            throw new InvalidDormitoryHierarchy('Room does not belong to this floor.');
        }

        foreach ($this->rooms as $existing) {
            if ($existing->id->equals($room->id)) {
                throw new InvalidDormitoryHierarchy('Room is already registered on this floor.');
            }
        }

        $this->rooms[] = $room;
    }

    /**
     * @return list<Room>
     */
    public function rooms(): array
    {
        return $this->rooms;
    }
}
