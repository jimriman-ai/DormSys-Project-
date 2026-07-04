<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Services;

use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Application\Contracts\Ports\DormitoryReadPort;
use App\Modules\Allocation\Application\Contracts\Ports\PhysicalStateSignalPort;
use App\Modules\Allocation\Domain\Enums\AllocationMethod;
use App\Modules\Allocation\Domain\Events\AllocationAssigned;
use App\Modules\Allocation\Domain\Events\AllocationCreated;
use App\Modules\Allocation\Domain\Exceptions\AllocationOverlapException;
use App\Modules\Allocation\Domain\Exceptions\BedNotAssignableException;
use App\Modules\Allocation\Domain\Models\Allocation;
use App\Modules\Allocation\Domain\ValueObjects\DateRange;
use App\Modules\Allocation\Domain\ValueObjects\PersonAllocationRef;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class CreateAllocationAction
{
    public function __construct(
        private readonly AllocationRepositoryContract $allocations,
        private readonly DormitoryReadPort $dormitory,
        private readonly PhysicalStateSignalPort $physicalState,
    ) {}

    public function execute(
        string $personId,
        string $bedId,
        DateTimeImmutable $start,
        DateTimeImmutable $end,
        AllocationMethod $method = AllocationMethod::Manual,
        ?string $sourceRequestId = null,
        ?string $sourceLotteryResultId = null,
    ): Allocation {
        if (! $this->dormitory->isBedAssignable($bedId)) {
            throw new BedNotAssignableException('Bed is not assignable.');
        }

        $allocation = Allocation::assign(
            personId: PersonAllocationRef::fromString($personId),
            bedId: $bedId,
            dateRange: DateRange::fromDates($start, $end),
            method: $method,
            sourceRequestId: $sourceRequestId,
            sourceLotteryResultId: $sourceLotteryResultId,
        );

        try {
            $persisted = DB::transaction(fn (): Allocation => $this->allocations->save($allocation));

            Event::dispatch(AllocationCreated::forAllocation($persisted));
            Event::dispatch(AllocationAssigned::forAllocation($persisted));

            $this->physicalState->reserveBed(
                bedId: $persisted->bedId,
                signalReferenceId: $persisted->requireId()->value,
            );
            $this->physicalState->occupyBed(
                bedId: $persisted->bedId,
                signalReferenceId: $persisted->requireId()->value,
            );

            return $persisted;
        } catch (AllocationOverlapException $exception) {
            throw $exception;
        }
    }
}
