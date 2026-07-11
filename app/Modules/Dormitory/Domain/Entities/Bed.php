<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Domain\Entities;

use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Domain\Exceptions\InvalidOccupancyTransition;
use App\Modules\Dormitory\Domain\Exceptions\InvalidResourceStateTransition;
use App\Modules\Dormitory\Domain\ValueObjects\BedId;
use App\Modules\Dormitory\Domain\ValueObjects\RoomId;

/**
 * Assignable physical sleeping unit. Owns bed-level status and physical occupancy.
 *
 * Allocation assignment is not physical occupancy and is not modeled here.
 */
final class Bed
{
    public function __construct(
        public readonly BedId $id,
        public readonly RoomId $roomId,
        public string $label,
        public ResourceStatus $status,
        public PhysicalOccupancyState $occupancy,
    ) {}

    public static function create(
        BedId $id,
        RoomId $roomId,
        string $label,
        ResourceStatus $status = ResourceStatus::Available,
        PhysicalOccupancyState $occupancy = PhysicalOccupancyState::Vacant,
    ): self {
        return new self(
            id: $id,
            roomId: $roomId,
            label: $label,
            status: $status,
            occupancy: $occupancy,
        );
    }

    public function belongsToRoom(RoomId $roomId): bool
    {
        return $this->roomId->equals($roomId);
    }

    public function isAssignable(): bool
    {
        return $this->status->contributesToAvailability()
            && $this->occupancy->isVacant();
    }

    public function changeStatus(ResourceStatus $newStatus): void
    {
        if ($this->occupancy->isOccupied() && $newStatus === ResourceStatus::Available) {
            throw new InvalidResourceStateTransition(
                'Occupied bed cannot be marked available without ending occupancy.',
            );
        }

        $this->status = $newStatus;
    }

    public function startOccupancy(): void
    {
        if (! $this->status->allowsOccupancy()) {
            throw new InvalidOccupancyTransition(
                sprintf('Bed with status "%s" cannot start occupancy.', $this->status->value),
            );
        }

        if ($this->occupancy->isOccupied()) {
            throw new InvalidOccupancyTransition('Occupied bed cannot start occupancy again.');
        }

        $this->occupancy = PhysicalOccupancyState::Occupied;
    }

    public function endOccupancy(): void
    {
        if (! $this->occupancy->isOccupied()) {
            throw new InvalidOccupancyTransition('Vacant bed cannot end occupancy.');
        }

        $this->occupancy = PhysicalOccupancyState::Vacant;
    }
}
