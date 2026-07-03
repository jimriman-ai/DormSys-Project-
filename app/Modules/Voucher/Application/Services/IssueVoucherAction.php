<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Services;

use App\Modules\Voucher\Application\Contracts\VoucherEligibilityRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherIssuanceContract;
use App\Modules\Voucher\Application\Contracts\VoucherLifecycleTransitionRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerRepositoryContract;
use App\Modules\Voucher\Domain\Enums\EligibilityOutcome;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Domain\Exceptions\VoucherNotEligibleForIssuanceException;
use App\Modules\Voucher\Domain\Exceptions\VoucherReissuanceRejectedException;
use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Domain\Models\VoucherLifecycleTransition;
use App\Modules\Voucher\Domain\Services\VoucherCodeGenerator;
use App\Modules\Voucher\Domain\ValueObjects\EligibilityOutcomeId;
use App\Modules\Voucher\Domain\ValueObjects\VoucherCode;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Database\QueryException;

final class IssueVoucherAction implements VoucherIssuanceContract
{
    private const int MAX_CODE_GENERATION_ATTEMPTS = 10;

    public function __construct(
        private readonly VoucherEligibilityRepositoryContract $eligibilityOutcomes,
        private readonly VoucherTriggerRepositoryContract $triggers,
        private readonly VoucherRepositoryContract $vouchers,
        private readonly VoucherLifecycleTransitionRepositoryContract $transitions,
        private readonly VoucherCodeGenerator $codeGenerator,
    ) {}

    public function issueFromEligibility(EligibilityOutcomeId $eligibilityOutcomeId): Voucher
    {
        $existing = $this->vouchers->findByEligibilityOutcomeId($eligibilityOutcomeId);

        if ($existing !== null) {
            if ($existing->isTerminal()) {
                throw new VoucherReissuanceRejectedException(
                    'Re-issuance from a terminal voucher state requires a new eligible evaluation.',
                );
            }

            return $existing;
        }

        $eligibility = $this->eligibilityOutcomes->findById($eligibilityOutcomeId);

        if ($eligibility === null || $eligibility->outcome !== EligibilityOutcome::Eligible) {
            throw new VoucherNotEligibleForIssuanceException(
                'Voucher issuance requires an eligible evaluation outcome.',
            );
        }

        $trigger = $this->triggers->findById($eligibility->triggerId);

        if ($trigger === null) {
            throw new \RuntimeException('Trigger not found for voucher issuance.');
        }

        $issuedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $code = $this->generateUniqueCode();

        $voucher = Voucher::issue(
            eligibilityOutcomeId: $eligibility->requireId(),
            triggerId: $trigger->requireId(),
            correlationId: $eligibility->correlationId,
            employeeId: $eligibility->employeeId,
            dormitoryId: $eligibility->dormitoryId,
            requestId: $eligibility->requestId,
            upstreamSource: $trigger->source,
            code: $code,
            stayPeriod: $trigger->stayPeriod,
            issuedAt: $issuedAt,
        );

        $saved = $this->saveWithCodeCollisionRetry($voucher);
        $this->recordTransition($saved, null, VoucherLifecycleState::Issued, $issuedAt);
        $this->triggers->markIssuancePathCompleted($trigger->requireId());

        return $saved;
    }

    private function generateUniqueCode(): VoucherCode
    {
        for ($attempt = 0; $attempt < self::MAX_CODE_GENERATION_ATTEMPTS; $attempt++) {
            $code = $this->codeGenerator->generate();

            if (! $this->vouchers->codeExists($code)) {
                return $code;
            }
        }

        throw new \RuntimeException('Unable to generate a globally unique voucher code.');
    }

    private function saveWithCodeCollisionRetry(Voucher $voucher): Voucher
    {
        $candidate = $voucher;

        for ($attempt = 0; $attempt < self::MAX_CODE_GENERATION_ATTEMPTS; $attempt++) {
            try {
                return $this->vouchers->save($candidate);
            } catch (QueryException $exception) {
                if (! $this->isUniqueViolation($exception)) {
                    throw $exception;
                }

                $candidate = Voucher::issue(
                    eligibilityOutcomeId: $candidate->eligibilityOutcomeId,
                    triggerId: $candidate->triggerId,
                    correlationId: $candidate->correlationId,
                    employeeId: $candidate->employeeId,
                    dormitoryId: $candidate->dormitoryId,
                    requestId: $candidate->requestId,
                    upstreamSource: $candidate->upstreamSource,
                    code: $this->generateUniqueCode(),
                    stayPeriod: $candidate->stayPeriod,
                    issuedAt: $candidate->issuedAt,
                );
            }
        }

        throw new \RuntimeException('Unable to persist voucher with a globally unique code.');
    }

    private function recordTransition(
        Voucher $voucher,
        ?VoucherLifecycleState $fromState,
        VoucherLifecycleState $toState,
        DateTimeImmutable $occurredAt,
    ): void {
        $this->transitions->save(VoucherLifecycleTransition::record(
            voucherId: $voucher->requireId(),
            fromState: $fromState,
            toState: $toState,
            correlationId: $voucher->correlationId,
            occurredAt: $occurredAt,
            payload: [
                'voucher_id' => $voucher->requireId()->value,
                'employee_id' => $voucher->employeeId,
                'dormitory_id' => $voucher->dormitoryId,
                'request_id' => $voucher->requestId,
                'correlation_id' => $voucher->correlationId->value,
                'code' => $voucher->code->value,
                'upstream_source' => $voucher->upstreamSource->value,
                'from_state' => $fromState?->value,
                'to_state' => $toState->value,
            ],
        ));
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return ($exception->errorInfo[0] ?? null) === '23505';
    }
}
