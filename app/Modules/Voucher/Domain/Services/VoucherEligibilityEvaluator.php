<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Services;

use App\Modules\Voucher\Domain\Enums\AccommodationClassification;
use App\Modules\Voucher\Domain\Enums\DeferredReasonCode;
use App\Modules\Voucher\Domain\Enums\EligibilityOutcome;
use App\Modules\Voucher\Domain\Enums\IneligibilityReasonCode;
use App\Modules\Voucher\Domain\Models\VoucherIssuanceTrigger;

final class VoucherEligibilityEvaluator
{
    /**
     * @return array{outcome: EligibilityOutcome, reasonCodes: list<string>, rationale: string}
     */
    public function evaluate(
        VoucherIssuanceTrigger $trigger,
        ?AccommodationClassification $dormitoryClassification,
    ): array {
        if ($this->isInternalAssignmentPath($trigger)) {
            return [
                'outcome' => EligibilityOutcome::Ineligible,
                'reasonCodes' => [IneligibilityReasonCode::InternalAssignmentPath->value],
                'rationale' => 'Trigger facts indicate an internal dormitory assignment path.',
            ];
        }

        if ($trigger->dormitoryId === null) {
            return [
                'outcome' => EligibilityOutcome::Ineligible,
                'reasonCodes' => [IneligibilityReasonCode::MissingDormitoryReference->value],
                'rationale' => 'Dormitory reference is required for external accommodation eligibility.',
            ];
        }

        if ($dormitoryClassification === null) {
            return [
                'outcome' => EligibilityOutcome::Deferred,
                'reasonCodes' => [DeferredReasonCode::ClassificationPending->value],
                'rationale' => 'External dormitory classification is not yet available from the accommodation catalog.',
            ];
        }

        if ($dormitoryClassification === AccommodationClassification::Internal) {
            return [
                'outcome' => EligibilityOutcome::Ineligible,
                'reasonCodes' => [IneligibilityReasonCode::NotExternalDormitory->value],
                'rationale' => 'Target dormitory is not classified as external accommodation.',
            ];
        }

        return [
            'outcome' => EligibilityOutcome::Eligible,
            'reasonCodes' => [],
            'rationale' => 'Trigger facts satisfy Voucher external accommodation eligibility rules.',
        ];
    }

    private function isInternalAssignmentPath(VoucherIssuanceTrigger $trigger): bool
    {
        $path = $trigger->upstreamFacts['assignment_path'] ?? null;

        if (is_string($path) && strtolower($path) === 'internal') {
            return true;
        }

        $intent = $trigger->upstreamFacts['voucher_intent'] ?? null;

        if (is_string($intent) && strtolower($intent) === 'internal_assignment') {
            return true;
        }

        $allocationOutcome = $trigger->upstreamFacts['allocation_outcome'] ?? null;

        return is_string($allocationOutcome)
            && strtolower($allocationOutcome) === 'successful_internal_assignment';
    }
}
