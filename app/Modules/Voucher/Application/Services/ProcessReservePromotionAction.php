<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Services;

use App\Modules\Voucher\Application\Contracts\ReservePromotionPathContract;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityEvaluationContract;
use App\Modules\Voucher\Application\Contracts\VoucherIssuanceContract;
use App\Modules\Voucher\Application\Contracts\VoucherLifecycleContract;
use App\Modules\Voucher\Application\Contracts\VoucherLifecycleTransitionRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\DTOs\InboundTriggerFactsDto;
use App\Modules\Voucher\Application\DTOs\ReservePromotionResultDto;
use App\Modules\Voucher\Application\DTOs\ReservePromotionTriggerFactsDto;
use App\Modules\Voucher\Domain\Enums\EligibilityOutcome;
use App\Modules\Voucher\Domain\Enums\ReservePromotionDisposition;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Domain\Exceptions\DuplicateTriggerCorrelationException;
use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Domain\Models\VoucherLifecycleTransition;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;
use DateTimeImmutable;
use DateTimeZone;

final class ProcessReservePromotionAction implements ReservePromotionPathContract
{
    public function __construct(
        private readonly VoucherTriggerIntakeContract $triggerIntake,
        private readonly VoucherRepositoryContract $vouchers,
        private readonly VoucherLifecycleContract $lifecycle,
        private readonly VoucherEligibilityEvaluationContract $eligibilityEvaluation,
        private readonly VoucherIssuanceContract $issuance,
        private readonly VoucherLifecycleTransitionRepositoryContract $transitions,
    ) {}

    public function processPromotion(ReservePromotionTriggerFactsDto $facts): ReservePromotionResultDto
    {
        if ($facts->isInternalProgram()) {
            return ReservePromotionResultDto::ignoredInternalProgram();
        }

        $occurredAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        try {
            $promotionTrigger = $this->triggerIntake->accept(
                InboundTriggerFactsDto::fromReservePromotionFacts(array_merge(
                    $facts->upstreamFacts,
                    [
                        'correlation_id' => $facts->correlationId->value,
                        'employee_id' => $this->resolvePromotionEmployeeId($facts),
                        'stay_start' => $this->resolvePromotionStayStart($facts),
                        'stay_end' => $this->resolvePromotionStayEnd($facts),
                        'dormitory_id' => $this->resolvePromotionDormitoryId($facts),
                    ],
                )),
            );
        } catch (DuplicateTriggerCorrelationException) {
            return ReservePromotionResultDto::duplicateRejected();
        }

        $priorWinner = $this->supersedePriorWinnerIfActive(
            VoucherId::fromString($facts->priorWinnerVoucherId),
            $occurredAt,
        );

        if (! $facts->hasReserveCandidate()) {
            $this->recordNoIssuanceOutcome($priorWinner, $facts, $occurredAt);

            return new ReservePromotionResultDto(
                disposition: ReservePromotionDisposition::NoEligibleReserves,
                promotionTrigger: $promotionTrigger,
                priorWinnerVoucher: $priorWinner,
            );
        }

        $reserveFacts = array_merge($facts->reserveFacts ?? [], [
            'program_id' => $facts->programId,
            'program_type' => $facts->programType,
            'promotion_correlation_id' => $facts->correlationId->value,
            'prior_winner_voucher_id' => $facts->priorWinnerVoucherId,
            'promotion_reason' => $facts->promotionReason,
        ]);

        try {
            $reserveTrigger = $this->triggerIntake->accept(
                InboundTriggerFactsDto::fromReservePromotionFacts($reserveFacts),
            );
        } catch (DuplicateTriggerCorrelationException) {
            $this->recordNoIssuanceOutcome($priorWinner, $facts, $occurredAt);

            return new ReservePromotionResultDto(
                disposition: ReservePromotionDisposition::DuplicateRejected,
                promotionTrigger: $promotionTrigger,
                priorWinnerVoucher: $priorWinner,
            );
        }

        $outcome = $this->eligibilityEvaluation->evaluateForTrigger($reserveTrigger->requireId());

        if ($outcome->outcome === EligibilityOutcome::Eligible) {
            $reserveVoucher = $this->issuance->issueFromEligibility($outcome->requireId());

            return new ReservePromotionResultDto(
                disposition: ReservePromotionDisposition::Issued,
                promotionTrigger: $promotionTrigger,
                priorWinnerVoucher: $priorWinner,
                reserveVoucher: $reserveVoucher,
                reserveEligibilityOutcome: $outcome,
            );
        }

        $disposition = $outcome->outcome === EligibilityOutcome::Deferred
            ? ReservePromotionDisposition::ReserveDeferred
            : ReservePromotionDisposition::ReserveIneligible;

        $this->recordNoIssuanceOutcome($priorWinner, $facts, $occurredAt, [
            'reserve_correlation_id' => $reserveFacts['correlation_id'] ?? null,
            'reserve_eligibility_outcome' => $outcome->outcome->value,
        ]);

        return new ReservePromotionResultDto(
            disposition: $disposition,
            promotionTrigger: $promotionTrigger,
            priorWinnerVoucher: $priorWinner,
            reserveEligibilityOutcome: $outcome,
        );
    }

    private function supersedePriorWinnerIfActive(VoucherId $voucherId, DateTimeImmutable $occurredAt): ?Voucher
    {
        $priorWinner = $this->vouchers->findById($voucherId);

        if ($priorWinner === null) {
            return null;
        }

        if ($priorWinner->lifecycleState !== VoucherLifecycleState::Issued) {
            return $priorWinner;
        }

        return $this->lifecycle->supersede($voucherId, $occurredAt);
    }

    /**
     * @param  array<string, mixed>  $extraPayload
     */
    private function recordNoIssuanceOutcome(
        ?Voucher $priorWinner,
        ReservePromotionTriggerFactsDto $facts,
        DateTimeImmutable $occurredAt,
        array $extraPayload = [],
    ): void {
        if ($priorWinner === null) {
            return;
        }

        $this->transitions->save(VoucherLifecycleTransition::recordPromotionOutcome(
            voucherId: $priorWinner->requireId(),
            voucherState: $priorWinner->lifecycleState,
            promotionCorrelationId: $facts->correlationId,
            promotionOutcome: 'no_issuance',
            occurredAt: $occurredAt,
            payload: array_merge($extraPayload, [
                'program_id' => $facts->programId,
                'promotion_reason' => $facts->promotionReason,
                'prior_winner_voucher_id' => $facts->priorWinnerVoucherId,
            ]),
        ));
    }

    private function resolvePromotionEmployeeId(ReservePromotionTriggerFactsDto $facts): string
    {
        $reserveEmployeeId = $facts->reserveFacts['employee_id'] ?? null;

        if (is_string($reserveEmployeeId) && trim($reserveEmployeeId) !== '') {
            return trim($reserveEmployeeId);
        }

        $priorWinner = $this->vouchers->findById(
            VoucherId::fromString($facts->priorWinnerVoucherId),
        );

        if ($priorWinner !== null) {
            return $priorWinner->employeeId;
        }

        throw new \RuntimeException('Reserve promotion facts require reserve employee_id or a resolvable prior winner.');
    }

    private function resolvePromotionStayStart(ReservePromotionTriggerFactsDto $facts): string
    {
        return $this->resolveStayBoundary($facts, 'stay_start');
    }

    private function resolvePromotionStayEnd(ReservePromotionTriggerFactsDto $facts): string
    {
        return $this->resolveStayBoundary($facts, 'stay_end');
    }

    private function resolvePromotionDormitoryId(ReservePromotionTriggerFactsDto $facts): ?string
    {
        $reserveDormitoryId = $facts->reserveFacts['dormitory_id'] ?? null;

        if (is_string($reserveDormitoryId) && trim($reserveDormitoryId) !== '') {
            return trim($reserveDormitoryId);
        }

        $priorWinner = $this->vouchers->findById(
            VoucherId::fromString($facts->priorWinnerVoucherId),
        );

        return $priorWinner?->dormitoryId;
    }

    private function resolveStayBoundary(ReservePromotionTriggerFactsDto $facts, string $key): string
    {
        $reserveValue = $facts->reserveFacts[$key] ?? null;

        if (is_string($reserveValue) && trim($reserveValue) !== '') {
            return trim($reserveValue);
        }

        $priorWinner = $this->vouchers->findById(
            VoucherId::fromString($facts->priorWinnerVoucherId),
        );

        if ($priorWinner !== null) {
            $date = $key === 'stay_start'
                ? $priorWinner->stayPeriod->start
                : $priorWinner->stayPeriod->end;

            return $date->format('Y-m-d');
        }

        throw new \RuntimeException("Reserve promotion facts require reserve {$key} or a resolvable prior winner.");
    }
}
