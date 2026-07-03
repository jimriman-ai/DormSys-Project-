<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Services;

use App\Modules\Voucher\Application\Contracts\ExternalLotteryWinnerPathContract;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityEvaluationContract;
use App\Modules\Voucher\Application\Contracts\VoucherIssuanceContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\DTOs\ExternalLotteryWinnerBatchDto;
use App\Modules\Voucher\Application\DTOs\ExternalLotteryWinnerBatchResultDto;
use App\Modules\Voucher\Application\DTOs\ExternalLotteryWinnerItemResultDto;
use App\Modules\Voucher\Application\DTOs\InboundTriggerFactsDto;
use App\Modules\Voucher\Domain\Enums\EligibilityOutcome;
use App\Modules\Voucher\Domain\Enums\ExternalLotteryBatchDisposition;
use App\Modules\Voucher\Domain\Enums\ExternalLotteryWinnerDisposition;
use App\Modules\Voucher\Domain\Exceptions\DuplicateTriggerCorrelationException;
use App\Modules\Voucher\Domain\Services\ExternalLotteryWinnerFactsSanitizer;
use App\Support\Exceptions\ValidationException;

final class ProcessExternalLotteryWinnerAction implements ExternalLotteryWinnerPathContract
{
    public function __construct(
        private readonly ExternalLotteryWinnerFactsSanitizer $sanitizer,
        private readonly VoucherTriggerIntakeContract $triggerIntake,
        private readonly VoucherEligibilityEvaluationContract $eligibilityEvaluation,
        private readonly VoucherIssuanceContract $issuance,
    ) {}

    public function processWinnerBatch(ExternalLotteryWinnerBatchDto $batch): ExternalLotteryWinnerBatchResultDto
    {
        if ($batch->isInternalProgram()) {
            return ExternalLotteryWinnerBatchResultDto::ignoredInternalProgram();
        }

        if (! $batch->drawCompleted) {
            throw new ValidationException(
                'External lottery winner facts require draw completion before processing.',
            );
        }

        $issuedCount = 0;
        $winnerResults = [];

        foreach ($batch->winnerFacts as $winnerFacts) {
            $correlationId = $this->extractCorrelationId($winnerFacts);

            if ($issuedCount >= $batch->programCapacity) {
                $winnerResults[] = new ExternalLotteryWinnerItemResultDto(
                    correlationId: $correlationId,
                    disposition: ExternalLotteryWinnerDisposition::SkippedCapacity,
                );

                continue;
            }

            $sanitizedFacts = $this->sanitizer->sanitize($winnerFacts);
            $sanitizedFacts['draw_completed'] = true;
            $sanitizedFacts['program_id'] = $batch->programId;
            $sanitizedFacts['program_type'] = $batch->programType;

            try {
                $trigger = $this->triggerIntake->accept(
                    InboundTriggerFactsDto::fromLotteryFacts($sanitizedFacts),
                );
            } catch (DuplicateTriggerCorrelationException) {
                $winnerResults[] = new ExternalLotteryWinnerItemResultDto(
                    correlationId: $correlationId,
                    disposition: ExternalLotteryWinnerDisposition::DuplicateRejected,
                );

                continue;
            }

            $outcome = $this->eligibilityEvaluation->evaluateForTrigger($trigger->requireId());

            if ($outcome->outcome === EligibilityOutcome::Eligible) {
                $voucher = $this->issuance->issueFromEligibility($outcome->requireId());
                $issuedCount++;
                $winnerResults[] = new ExternalLotteryWinnerItemResultDto(
                    correlationId: $correlationId,
                    disposition: ExternalLotteryWinnerDisposition::Issued,
                    voucher: $voucher,
                    eligibilityOutcome: $outcome,
                );

                continue;
            }

            $disposition = $outcome->outcome === EligibilityOutcome::Deferred
                ? ExternalLotteryWinnerDisposition::Deferred
                : ExternalLotteryWinnerDisposition::NotEligible;

            $winnerResults[] = new ExternalLotteryWinnerItemResultDto(
                correlationId: $correlationId,
                disposition: $disposition,
                eligibilityOutcome: $outcome,
            );
        }

        return new ExternalLotteryWinnerBatchResultDto(
            batchDisposition: ExternalLotteryBatchDisposition::Processed,
            issuedCount: $issuedCount,
            winnerResults: $winnerResults,
        );
    }

    /**
     * @param  array<string, mixed>  $facts
     */
    private function extractCorrelationId(array $facts): string
    {
        $value = $facts['correlation_id'] ?? null;

        if (! is_string($value) || trim($value) === '') {
            throw new ValidationException('Each external lottery winner requires a correlation_id.');
        }

        return trim($value);
    }
}
