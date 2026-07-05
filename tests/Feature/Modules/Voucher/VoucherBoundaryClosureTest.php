<?php

declare(strict_types=1);

use App\Modules\Voucher\Application\Contracts\AccommodationClassificationReadPort;
use App\Modules\Voucher\Application\Contracts\ExternalLotteryWinnerPathContract;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityEvaluationContract;
use App\Modules\Voucher\Application\Contracts\VoucherIssuanceContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\DTOs\ExternalLotteryWinnerBatchDto;
use App\Modules\Voucher\Application\DTOs\InboundTriggerFactsDto;
use App\Modules\Voucher\Domain\Enums\AccommodationClassification;
use App\Modules\Voucher\Domain\Enums\EligibilityOutcome;
use App\Modules\Voucher\Domain\Enums\ExternalLotteryBatchDisposition;
use App\Modules\Voucher\Domain\Enums\IneligibilityReasonCode;
use App\Modules\Voucher\Infrastructure\Adapters\InMemoryAccommodationClassificationReadAdapter;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherIssuanceTriggerModel;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @param  array<string, AccommodationClassification>  $classifications
 */
function bindBoundaryClassification(array $classifications): void
{
    app()->instance(
        AccommodationClassificationReadPort::class,
        new InMemoryAccommodationClassificationReadAdapter($classifications),
    );
}

it('persists upstream trigger facts without upstream operational data ownership (SC-005)', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $lotteryResultId = UuidGenerator::uuid7();
    bindBoundaryClassification([$dormitoryId => AccommodationClassification::External]);

    $upstreamFacts = [
        'correlation_id' => 'boundary-facts-001',
        'employee_id' => $employeeId,
        'dormitory_id' => $dormitoryId,
        'stay_start' => '2026-09-01',
        'stay_end' => '2026-09-30',
        'lottery_result_id' => $lotteryResultId,
        'upstream_claims_eligible' => true,
    ];

    app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromLotteryFacts($upstreamFacts),
    );

    $stored = VoucherIssuanceTriggerModel::query()
        ->where('correlation_id', 'boundary-facts-001')
        ->first();
    if (! $stored instanceof VoucherIssuanceTriggerModel) {
        throw new UnexpectedValueException('Expected stored trigger facts.');
    }

    expect($stored->upstream_facts)->toHaveKey('lottery_result_id', $lotteryResultId);
    expect($stored->upstream_facts)->toHaveKey('upstream_claims_eligible', true);
    expect($stored->upstream_facts)->not->toHaveKey('allocation_id');
    expect($stored->upstream_facts)->not->toHaveKey('bed_id');
});

it('processes voucher flows from synthetic upstream facts only (R8)', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    bindBoundaryClassification([$dormitoryId => AccommodationClassification::External]);

    $trigger = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromAllocationFacts([
            'correlation_id' => 'boundary-synthetic-001',
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => '2026-10-01',
            'stay_end' => '2026-10-31',
            'allocation_trigger_reason' => 'unfulfilled_accommodation',
        ]),
    );

    $outcome = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());
    $voucher = app(VoucherIssuanceContract::class)->issueFromEligibility($outcome->requireId());

    expect($outcome->outcome)->toBe(EligibilityOutcome::Eligible);
    expect($voucher->lifecycleState->value)->toBe('issued');
    expect(VoucherModel::query()->count())->toBe(1);
});

it('does not issue vouchers for internal dormitory lottery programs (BR-13)', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    bindBoundaryClassification([$dormitoryId => AccommodationClassification::External]);

    $result = app(ExternalLotteryWinnerPathContract::class)->processWinnerBatch(
        ExternalLotteryWinnerBatchDto::fromUpstreamFacts([
            'program_id' => 'boundary-internal-program',
            'program_type' => 'internal',
            'draw_completed' => true,
            'program_capacity' => 5,
            'winners' => [[
                'correlation_id' => 'boundary-internal-winner',
                'employee_id' => UuidGenerator::uuid7(),
                'dormitory_id' => $dormitoryId,
                'stay_start' => '2026-09-01',
                'stay_end' => '2026-09-30',
            ]],
        ]),
    );

    expect($result->batchDisposition)->toBe(ExternalLotteryBatchDisposition::IgnoredInternalProgram);
    expect(VoucherModel::query()->count())->toBe(0);
});

it('does not issue vouchers for internal dormitory classification triggers (FR-005)', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    bindBoundaryClassification([$dormitoryId => AccommodationClassification::Internal]);

    $trigger = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromLotteryFacts([
            'correlation_id' => 'boundary-internal-dorm-001',
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => '2026-09-01',
            'stay_end' => '2026-09-30',
        ]),
    );

    $outcome = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    expect($outcome->outcome)->toBe(EligibilityOutcome::Ineligible);
    expect($outcome->reasonCodes)->toContain(IneligibilityReasonCode::NotExternalDormitory->value);
    expect(VoucherModel::query()->count())->toBe(0);
});

it('does not issue vouchers for successful internal allocation triggers (BR-13)', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    bindBoundaryClassification([$dormitoryId => AccommodationClassification::External]);

    $trigger = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromAllocationFacts([
            'correlation_id' => 'boundary-internal-allocation-001',
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => '2026-09-01',
            'stay_end' => '2026-09-30',
            'allocation_outcome' => 'successful_internal_assignment',
        ]),
    );

    $outcome = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    expect($outcome->outcome)->toBe(EligibilityOutcome::Ineligible);
    expect($outcome->reasonCodes)->toContain(IneligibilityReasonCode::InternalAssignmentPath->value);
    expect(VoucherModel::query()->count())->toBe(0);
});
