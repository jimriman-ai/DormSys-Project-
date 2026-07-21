<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\CheckIn\Application\Contracts\AllocationAssignmentReadPort;
use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Application\Contracts\RequestStayLifecycleCommandPort;
use App\Modules\CheckIn\Domain\Events\CheckedIn;
use App\Modules\CheckIn\Domain\Exceptions\AllocationNotActiveException;
use App\Modules\CheckIn\Domain\Exceptions\OpenCheckInRecordExistsException;
use App\Modules\CheckIn\Domain\Models\CheckInRecord;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class CheckInAction
{
    public function __construct(
        private readonly AllocationAssignmentReadPort $allocations,
        private readonly CheckInRecordRepositoryContract $records,
        private readonly RequestStayLifecycleCommandPort $requestStayLifecycle,
        private readonly OperatorRoleGate $operatorGate,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly CheckInMutationAuthorizationGate $checkInMutationAuth,
    ) {}

    public function execute(string $allocationId, string $operatorId): CheckInRecord
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::CHECKIN_CREATE, [
            'allocationId' => $allocationId,
            'operatorId' => $operatorId,
        ]);
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::CHECKIN_OPERATE, [
            'allocationId' => $allocationId,
            'operatorId' => $operatorId,
        ]);
        $this->checkInMutationAuth->assertCreate($operatorId);
        $this->checkInMutationAuth->assertOperate($operatorId);
        $this->operatorGate->assertOperator($operatorId);

        if (! $this->allocations->hasActiveAllocation($allocationId)) {
            throw new AllocationNotActiveException('Active allocation is required for check-in.');
        }

        if ($this->records->findOpenByAllocationId($allocationId) !== null) {
            throw new OpenCheckInRecordExistsException('An open check-in record already exists for this allocation.');
        }

        return DB::transaction(function () use ($allocationId, $operatorId): CheckInRecord {
            $record = CheckInRecord::open(
                allocationId: $allocationId,
                operatorId: $operatorId,
                checkedInAt: new DateTimeImmutable('now', new DateTimeZone('UTC')),
            );

            $persisted = $this->records->save($record);

            // OA-05-03 / DEBT-W3-01: advance Request status when allocation is request-sourced.
            $this->requestStayLifecycle->markCheckedInForAllocation($allocationId);

            Event::dispatch(CheckedIn::forRecord($persisted));

            return $persisted;
        });
    }
}
