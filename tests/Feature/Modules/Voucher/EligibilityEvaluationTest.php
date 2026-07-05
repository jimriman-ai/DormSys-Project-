<?php

declare(strict_types=1);

use App\Modules\Voucher\Application\Contracts\AccommodationClassificationReadPort;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityEvaluationContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\DTOs\InboundTriggerFactsDto;
use App\Modules\Voucher\Domain\Enums\AccommodationClassification;
use App\Modules\Voucher\Domain\Enums\DeferredReasonCode;
use App\Modules\Voucher\Domain\Enums\EligibilityOutcome;
use App\Modules\Voucher\Domain\Enums\IneligibilityReasonCode;
use App\Modules\Voucher\Domain\Models\VoucherIssuanceTrigger;
use App\Modules\Voucher\Infrastructure\Adapters\InMemoryAccommodationClassificationReadAdapter;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * @param  array<string, AccommodationClassification>  $classifications
 */
function bindClassificationAdapter(array $classifications): void
{
    app()->instance(
        AccommodationClassificationReadPort::class,
        new InMemoryAccommodationClassificationReadAdapter($classifications),
    );
}

/**
 * @param  array<string, mixed>  $extraFacts
 */
function acceptLotteryTrigger(
    string $correlationId,
    string $employeeId,
    ?string $dormitoryId = null,
    array $extraFacts = [],
): VoucherIssuanceTrigger {
    return app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromLotteryFacts(array_merge([
            'correlation_id' => $correlationId,
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => '2026-09-01',
            'stay_end' => '2026-09-30',
        ], $extraFacts)),
    );
}

it('produces eligible outcome for external lottery trigger facts with rationale', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    bindClassificationAdapter([$dormitoryId => AccommodationClassification::External]);

    $trigger = acceptLotteryTrigger('lottery-eligible-001', $employeeId, $dormitoryId);
    $outcome = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    expect($outcome->outcome)->toBe(EligibilityOutcome::Eligible);
    expect($outcome->rationale)->not->toBe('');
    expect($outcome->reasonCodes)->toBe([]);
});

it('produces ineligible outcome when dormitory reference is missing', function (): void {
    $employeeId = UuidGenerator::uuid7();

    $trigger = acceptLotteryTrigger('lottery-missing-dorm-001', $employeeId);
    $outcome = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    expect($outcome->outcome)->toBe(EligibilityOutcome::Ineligible);
    expect($outcome->reasonCodes)->toContain(IneligibilityReasonCode::MissingDormitoryReference->value);
});

it('produces ineligible outcome when dormitory is not external', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    bindClassificationAdapter([$dormitoryId => AccommodationClassification::Internal]);

    $trigger = acceptLotteryTrigger('lottery-internal-dorm-001', $employeeId, $dormitoryId);
    $outcome = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    expect($outcome->outcome)->toBe(EligibilityOutcome::Ineligible);
    expect($outcome->reasonCodes)->toContain(IneligibilityReasonCode::NotExternalDormitory->value);
});

it('produces deferred outcome when classification is unavailable', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    bindClassificationAdapter([]);

    $trigger = acceptLotteryTrigger('lottery-deferred-001', $employeeId, $dormitoryId);
    $outcome = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    expect($outcome->outcome)->toBe(EligibilityOutcome::Deferred);
    expect($outcome->reasonCodes)->toContain(DeferredReasonCode::ClassificationPending->value);
});

it('applies voucher eligibility rules for allocation unfulfilled triggers', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = UuidGenerator::uuid7();
    bindClassificationAdapter([$dormitoryId => AccommodationClassification::External]);

    $trigger = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromAllocationFacts([
            'correlation_id' => 'allocation-eligible-001',
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'request_id' => $requestId,
            'stay_start' => '2026-10-01',
            'stay_end' => '2026-10-31',
            'allocation_trigger_reason' => 'unfulfilled_accommodation',
            'upstream_claims_eligible' => true,
        ]),
    );

    $outcome = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    expect($outcome->outcome)->toBe(EligibilityOutcome::Eligible);
    expect($outcome->rationale)->toContain('Voucher');
});

it('rejects internal assignment path triggers as ineligible', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    bindClassificationAdapter([$dormitoryId => AccommodationClassification::External]);

    $trigger = acceptLotteryTrigger('lottery-internal-path-001', $employeeId, $dormitoryId, [
        'assignment_path' => 'internal',
    ]);
    $outcome = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    expect($outcome->outcome)->toBe(EligibilityOutcome::Ineligible);
    expect($outcome->reasonCodes)->toContain(IneligibilityReasonCode::InternalAssignmentPath->value);
});

it('confirms external classification via accommodation catalog read port', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $adapter = new InMemoryAccommodationClassificationReadAdapter([
        $dormitoryId => AccommodationClassification::External,
    ]);
    app()->instance(AccommodationClassificationReadPort::class, $adapter);

    $trigger = acceptLotteryTrigger('lottery-catalog-001', $employeeId, $dormitoryId);
    app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    expect($adapter->getClassification($dormitoryId))->toBe(AccommodationClassification::External);
});

it('records immutable employee dormitory request and correlation references', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = UuidGenerator::uuid7();
    bindClassificationAdapter([$dormitoryId => AccommodationClassification::External]);

    $trigger = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromAllocationFacts([
            'correlation_id' => 'immutable-refs-001',
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'request_id' => $requestId,
            'stay_start' => '2026-11-01',
            'stay_end' => '2026-11-30',
            'allocation_trigger_reason' => 'unfulfilled_accommodation',
        ]),
    );

    $outcome = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    expect($outcome->employeeId)->toBe($employeeId);
    expect($outcome->dormitoryId)->toBe($dormitoryId);
    expect($outcome->requestId)->toBe($requestId);
    expect($outcome->correlationId->value)->toBe('immutable-refs-001');
    expect($outcome->triggerId->value)->toBe($trigger->requireId()->value);
});

it('runs voucher eligibility outcome migrations', function (): void {
    expect(Schema::hasTable('voucher_eligibility_outcomes'))->toBeTrue();
});
