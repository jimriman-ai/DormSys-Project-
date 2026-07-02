<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Services;

use App\Modules\Voucher\Application\Contracts\AccommodationClassificationReadPort;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityEvaluationContract;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerRepositoryContract;
use App\Modules\Voucher\Domain\Models\VoucherEligibilityOutcome;
use App\Modules\Voucher\Domain\Services\VoucherEligibilityEvaluator;
use App\Modules\Voucher\Domain\ValueObjects\TriggerId;
use DateTimeImmutable;
use DateTimeZone;

final class EvaluateVoucherEligibilityAction implements VoucherEligibilityEvaluationContract
{
    public function __construct(
        private readonly VoucherTriggerRepositoryContract $triggers,
        private readonly VoucherEligibilityRepositoryContract $outcomes,
        private readonly AccommodationClassificationReadPort $classificationReadPort,
        private readonly VoucherEligibilityEvaluator $evaluator,
    ) {}

    public function evaluateForTrigger(TriggerId $triggerId): VoucherEligibilityOutcome
    {
        $existing = $this->outcomes->findByTriggerId($triggerId);

        if ($existing !== null) {
            return $existing;
        }

        $trigger = $this->triggers->findById($triggerId);

        if ($trigger === null) {
            throw new \RuntimeException('Trigger not found for eligibility evaluation.');
        }

        $classification = $trigger->dormitoryId !== null
            ? $this->classificationReadPort->getClassification($trigger->dormitoryId)
            : null;

        $result = $this->evaluator->evaluate($trigger, $classification);

        $outcome = VoucherEligibilityOutcome::record(
            triggerId: $trigger->requireId(),
            correlationId: $trigger->correlationId,
            employeeId: $trigger->employeeId,
            dormitoryId: $trigger->dormitoryId,
            requestId: $trigger->requestId,
            outcome: $result['outcome'],
            reasonCodes: $result['reasonCodes'],
            rationale: $result['rationale'],
            evaluatedAt: new DateTimeImmutable('now', new DateTimeZone('UTC')),
        );

        return $this->outcomes->save($outcome);
    }
}
