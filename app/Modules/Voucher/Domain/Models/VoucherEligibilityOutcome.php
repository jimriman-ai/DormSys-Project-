<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Models;

use App\Modules\Voucher\Domain\Enums\EligibilityOutcome;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Domain\ValueObjects\EligibilityOutcomeId;
use App\Modules\Voucher\Domain\ValueObjects\TriggerId;
use DateTimeImmutable;

final class VoucherEligibilityOutcome
{
    /**
     * @param  list<string>  $reasonCodes
     */
    public function __construct(
        public readonly ?EligibilityOutcomeId $id,
        public readonly TriggerId $triggerId,
        public readonly CorrelationId $correlationId,
        public readonly string $employeeId,
        public readonly ?string $dormitoryId,
        public readonly ?string $requestId,
        public readonly EligibilityOutcome $outcome,
        public readonly array $reasonCodes,
        public readonly string $rationale,
        public readonly DateTimeImmutable $evaluatedAt,
    ) {}

    /**
     * @param  list<string>  $reasonCodes
     */
    public static function record(
        TriggerId $triggerId,
        CorrelationId $correlationId,
        string $employeeId,
        ?string $dormitoryId,
        ?string $requestId,
        EligibilityOutcome $outcome,
        array $reasonCodes,
        string $rationale,
        DateTimeImmutable $evaluatedAt,
    ): self {
        return new self(
            id: null,
            triggerId: $triggerId,
            correlationId: $correlationId,
            employeeId: $employeeId,
            dormitoryId: $dormitoryId,
            requestId: $requestId,
            outcome: $outcome,
            reasonCodes: $reasonCodes,
            rationale: $rationale,
            evaluatedAt: $evaluatedAt,
        );
    }

    public function requireId(): EligibilityOutcomeId
    {
        if ($this->id === null) {
            throw new \LogicException('Eligibility outcome identifier is not assigned.');
        }

        return $this->id;
    }
}
