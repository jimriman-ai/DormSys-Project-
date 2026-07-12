<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Services;

use App\Modules\Dormitory\Application\Contracts\AllocationBedPhysicalStateRepositoryContract;
use App\Modules\Dormitory\Application\Contracts\AllocationPhysicalStateApplicationContract;
use App\Modules\Dormitory\Application\DTOs\ApplyPhysicalStateSignalCommand;
use App\Modules\Dormitory\Application\DTOs\ApplyPhysicalStateSignalResult;
use App\Modules\Dormitory\Application\Enums\PhysicalStateSignalType;
use App\Modules\Dormitory\Domain\Entities\Bed;
use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Exceptions\InvalidOccupancyTransition;
use App\Modules\Dormitory\Domain\ValueObjects\BedId;
use App\Modules\Dormitory\Domain\ValueObjects\RoomId;
use Illuminate\Support\Facades\DB;

final class AllocationPhysicalStateApplicationService implements AllocationPhysicalStateApplicationContract
{
    public function __construct(
        private readonly AllocationBedPhysicalStateRepositoryContract $beds,
    ) {}

    public function apply(ApplyPhysicalStateSignalCommand $command): ApplyPhysicalStateSignalResult
    {
        return DB::transaction(function () use ($command): ApplyPhysicalStateSignalResult {
            $row = $this->beds->findBed($command->bedId);

            if ($row === null) {
                return new ApplyPhysicalStateSignalResult(
                    accepted: false,
                    resultingState: PhysicalOccupancyState::Vacant,
                    rejectionCode: 'bed_not_found',
                );
            }

            // Release against already-VACANT is a safe no-op (idempotent unwind).
            if ($command->signalType === PhysicalStateSignalType::Release && $row['occupancy']->isVacant()) {
                return new ApplyPhysicalStateSignalResult(
                    accepted: true,
                    resultingState: PhysicalOccupancyState::Vacant,
                    rejectionCode: null,
                );
            }

            if ($this->isIdempotentReplay($row, $command)) {
                return new ApplyPhysicalStateSignalResult(
                    accepted: true,
                    resultingState: $row['occupancy'],
                    rejectionCode: null,
                );
            }

            $bed = Bed::create(
                id: BedId::fromString($row['id']),
                roomId: RoomId::fromString($row['room_id']),
                label: $row['label'],
                status: $row['status'],
                occupancy: $row['occupancy'],
            );

            try {
                match ($command->signalType) {
                    PhysicalStateSignalType::Reserve => $bed->reserve(),
                    PhysicalStateSignalType::OccupyMarker => $bed->applyOccupyMarker(),
                    PhysicalStateSignalType::Release => $bed->releaseInventoryMarker(),
                };
            } catch (InvalidOccupancyTransition) {
                return new ApplyPhysicalStateSignalResult(
                    accepted: false,
                    resultingState: $row['occupancy'],
                    rejectionCode: 'illegal_transition',
                );
            }

            $signalRef = $command->signalType === PhysicalStateSignalType::Release
                ? null
                : $command->correlationId;

            $this->beds->updateOccupancy(
                bedId: $bed->id->value,
                occupancy: $bed->occupancy,
                lastSignalReferenceId: $signalRef,
            );

            return new ApplyPhysicalStateSignalResult(
                accepted: true,
                resultingState: $bed->occupancy,
                rejectionCode: null,
            );
        });
    }

    /**
     * @param  array{
     *     occupancy: PhysicalOccupancyState,
     *     last_signal_reference_id: string|null
     * }  $row
     */
    private function isIdempotentReplay(array $row, ApplyPhysicalStateSignalCommand $command): bool
    {
        if ($command->correlationId === null || $command->correlationId === '') {
            return false;
        }

        if ($row['last_signal_reference_id'] !== $command->correlationId) {
            return false;
        }

        return match ($command->signalType) {
            PhysicalStateSignalType::Reserve => $row['occupancy']->isReserved(),
            PhysicalStateSignalType::OccupyMarker => $row['occupancy']->isOccupied(),
            PhysicalStateSignalType::Release => $row['occupancy']->isVacant(),
        };
    }
}
