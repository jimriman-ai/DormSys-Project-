<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Application\Contracts\RequestStayLifecycleCommandPort;
use App\Modules\CheckIn\Domain\Events\CheckedOut;
use App\Modules\CheckIn\Domain\Exceptions\NoOpenCheckInRecordException;
use App\Modules\CheckIn\Domain\Models\CheckInRecord;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class CheckOutAction
{
    public function __construct(
        private readonly CheckInRecordRepositoryContract $records,
        private readonly RequestStayLifecycleCommandPort $requestStayLifecycle,
        private readonly OperatorRoleGate $operatorGate,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly CheckInMutationAuthorizationGate $checkInMutationAuth,
    ) {}

    public function execute(string $allocationId, string $operatorId): CheckInRecord
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::CHECKIN_CLOSE, [
            'allocationId' => $allocationId,
            'operatorId' => $operatorId,
        ]);
        $this->checkInMutationAuth->assertClose($operatorId);
        $this->operatorGate->assertOperator($operatorId);

        $openRecord = $this->records->findOpenByAllocationId($allocationId);

        if ($openRecord === null) {
            throw new NoOpenCheckInRecordException('An open check-in record is required for check-out.');
        }

        return DB::transaction(function () use ($allocationId, $openRecord): CheckInRecord {
            $checkedOut = $openRecord->withCheckOut(
                new DateTimeImmutable('now', new DateTimeZone('UTC')),
            );

            $persisted = $this->records->save($checkedOut);

            // OA-05-03 / DEBT-W3-01: advance Request status when allocation is request-sourced.
            $this->requestStayLifecycle->markCheckedOutForAllocation($allocationId);

            Event::dispatch(CheckedOut::forRecord($persisted));

            return $persisted;
        });
    }
}
