<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Domain\Entities;

use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Domain\Exceptions\InvalidCapacity;
use App\Modules\Dormitory\Domain\Exceptions\InvalidDormitoryHierarchy;
use App\Modules\Dormitory\Domain\ValueObjects\Availability;
use App\Modules\Dormitory\Domain\ValueObjects\Capacity;
use App\Modules\Dormitory\Domain\ValueObjects\FloorId;
use App\Modules\Dormitory\Domain\ValueObjects\RoomId;

/**
 * Physical room. Owns room-level capacity, status, and bed collection.
 */
final class Room
{
    /** @var list<Bed> */
    private array $beds = [];

    public function __construct(
        public readonly RoomId $id,
        public readonly FloorId $floorId,
        public string $code,
        public string $name,
        public Capacity $capacity,
        public ResourceStatus $status,
    ) {}

    public static function create(
        RoomId $id,
        FloorId $floorId,
        string $code,
        string $name,
        Capacity $capacity,
        ResourceStatus $status = ResourceStatus::Available,
    ): self {
        return new self(
            id: $id,
            floorId: $floorId,
            code: $code,
            name: $name,
            capacity: $capacity,
            status: $status,
        );
    }

    public function belongsToFloor(FloorId $floorId): bool
    {
        return $this->floorId->equals($floorId);
    }

    public function registerBed(Bed $bed): void
    {
        if (! $bed->belongsToRoom($this->id)) {
            throw new InvalidDormitoryHierarchy('Bed does not belong to this room.');
        }

        if (count($this->beds) >= $this->capacity->total) {
            throw new InvalidCapacity('Room cannot register more beds than its total capacity.');
        }

        foreach ($this->beds as $existing) {
            if ($existing->id->equals($bed->id)) {
                throw new InvalidDormitoryHierarchy('Bed is already registered in this room.');
            }
        }

        $this->beds[] = $bed;
    }

    public function changeStatus(ResourceStatus $newStatus): void
    {
        $this->status = $newStatus;
    }

    /**
     * @return list<Bed>
     */
    public function beds(): array
    {
        return $this->beds;
    }

    public function bedCount(): int
    {
        return count($this->beds);
    }

    public function calculateAvailability(): Availability
    {
        if (! $this->status->contributesToAvailability()) {
            return Availability::none();
        }

        $availableCount = 0;

        foreach ($this->beds as $bed) {
            if ($bed->isAssignable()) {
                $availableCount++;
            }
        }

        return Availability::of($availableCount);
    }

    public function occupiedBedCount(): int
    {
        $occupied = 0;

        foreach ($this->beds as $bed) {
            if ($bed->occupancy->isOccupied()) {
                $occupied++;
            }
        }

        return $occupied;
    }

    public function currentCapacity(): Capacity
    {
        return $this->capacity->withOccupied($this->occupiedBedCount());
    }
}
