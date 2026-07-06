<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Domain\Events\CheckedOut;
use App\Modules\CheckIn\Domain\Exceptions\NoOpenCheckInRecordException;
use App\Modules\CheckIn\Domain\Models\CheckInRecord;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\Event;

final class CheckOutAction
{
    public function __construct(
        private readonly CheckInRecordRepositoryContract $records,
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

        $checkedOut = $openRecord->withCheckOut(
            new DateTimeImmutable('now', new DateTimeZone('UTC')),
        );

        $persisted = $this->records->save($checkedOut);

        Event::dispatch(CheckedOut::forRecord($persisted));

        return $persisted;
    }
}
