<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Services;

use App\Modules\CheckIn\Application\Contracts\AllocationAssignmentReadPort;
use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Domain\Events\CheckedIn;
use App\Modules\CheckIn\Domain\Exceptions\AllocationNotActiveException;
use App\Modules\CheckIn\Domain\Exceptions\OpenCheckInRecordExistsException;
use App\Modules\CheckIn\Domain\Models\CheckInRecord;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\Event;

final class CheckInAction
{
    public function __construct(
        private readonly AllocationAssignmentReadPort $allocations,
        private readonly CheckInRecordRepositoryContract $records,
        private readonly OperatorRoleGate $operatorGate,
    ) {}

    public function execute(string $allocationId, string $operatorId): CheckInRecord
    {
        $this->operatorGate->assertOperator($operatorId);

        if (! $this->allocations->hasActiveAllocation($allocationId)) {
            throw new AllocationNotActiveException('Active allocation is required for check-in.');
        }

        if ($this->records->findOpenByAllocationId($allocationId) !== null) {
            throw new OpenCheckInRecordExistsException('An open check-in record already exists for this allocation.');
        }

        $record = CheckInRecord::open(
            allocationId: $allocationId,
            operatorId: $operatorId,
            checkedInAt: new DateTimeImmutable('now', new DateTimeZone('UTC')),
        );

        $persisted = $this->records->save($record);

        Event::dispatch(CheckedIn::forRecord($persisted));

        return $persisted;
    }
}
