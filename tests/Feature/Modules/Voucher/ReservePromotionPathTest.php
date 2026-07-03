<?php

declare(strict_types=1);

use App\Modules\Voucher\Application\Contracts\AccommodationClassificationReadPort;
use App\Modules\Voucher\Application\Contracts\ReservePromotionPathContract;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityEvaluationContract;
use App\Modules\Voucher\Application\Contracts\VoucherIssuanceContract;
use App\Modules\Voucher\Application\Contracts\VoucherLifecycleTransitionRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\DTOs\InboundTriggerFactsDto;
use App\Modules\Voucher\Application\DTOs\ReservePromotionTriggerFactsDto;
use App\Modules\Voucher\Application\Services\EvaluateVoucherEligibilityAction;
use App\Modules\Voucher\Domain\Enums\AccommodationClassification;
use App\Modules\Voucher\Domain\Enums\ReservePromotionDisposition;
use App\Modules\Voucher\Domain\Enums\TriggerIntakeStatus;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Infrastructure\Adapters\InMemoryAccommodationClassificationReadAdapter;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherIssuanceTriggerModel;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function bindReservePromotionClassifications(array $classifications): void
{
    app()->instance(
        AccommodationClassificationReadPort::class,
        new InMemoryAccommodationClassificationReadAdapter($classifications),
    );
}

function issueEligibleVoucherForReservePromotion(
    string $correlationId,
    string $employeeId,
    string $dormitoryId,
): object {
    $trigger = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromLotteryFacts([
            'correlation_id' => $correlationId,
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => '2026-09-01',
            'stay_end' => '2026-09-30',
        ]),
    );
    $eligibility = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    return app(VoucherIssuanceContract::class)->issueFromEligibility($eligibility->requireId());
}

function issueExternalLotteryWinner(string $correlationId, string $employeeId, string $dormitoryId): object
{
    bindReservePromotionClassifications([$dormitoryId => AccommodationClassification::External]);

    return issueEligibleVoucherForReservePromotion($correlationId, $employeeId, $dormitoryId);
}

function reservePromotionFacts(
    string $promotionCorrelationId,
    string $programId,
    object $priorWinnerVoucher,
    ?array $reserve = null,
    string $programType = 'external',
): ReservePromotionTriggerFactsDto {
    $facts = [
        'correlation_id' => $promotionCorrelationId,
        'program_id' => $programId,
        'program_type' => $programType,
        'prior_winner_voucher_id' => $priorWinnerVoucher->requireId()->value,
        'promotion_reason' => 'winner_decline',
    ];

    if ($reserve !== null) {
        $facts['reserve'] = $reserve;
    }

    return ReservePromotionTriggerFactsDto::fromUpstreamFacts($facts);
}

it('accepts reserve promotion trigger facts for external lottery programs', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $winnerEmployeeId = UuidGenerator::uuid7();
    $reserveEmployeeId = UuidGenerator::uuid7();
    $winner = issueExternalLotteryWinner('winner-promo-001', $winnerEmployeeId, $dormitoryId);

    $result = app(ReservePromotionPathContract::class)->processPromotion(
        reservePromotionFacts('promo-accept-001', 'prog-external-promo', $winner, [
            'correlation_id' => 'reserve-accept-001',
            'employee_id' => $reserveEmployeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => '2026-09-01',
            'stay_end' => '2026-09-30',
        ]),
    );

    expect($result->promotionTrigger)->not->toBeNull();
    expect($result->promotionTrigger?->upstreamFacts)->toHaveKey('trigger_kind', 'reserve_promotion');
    expect($result->promotionTrigger?->status)->toBe(TriggerIntakeStatus::Accepted);

    $stored = VoucherIssuanceTriggerModel::query()
        ->where('correlation_id', 'promo-accept-001')
        ->first();

    expect($stored)->not->toBeNull();
    expect($stored->upstream_facts['trigger_kind'])->toBe('reserve_promotion');
});

it('supersedes active winner voucher before reserve issuance', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $winnerEmployeeId = UuidGenerator::uuid7();
    $reserveEmployeeId = UuidGenerator::uuid7();
    $winner = issueExternalLotteryWinner('winner-supersede-001', $winnerEmployeeId, $dormitoryId);

    $result = app(ReservePromotionPathContract::class)->processPromotion(
        reservePromotionFacts('promo-supersede-001', 'prog-supersede', $winner, [
            'correlation_id' => 'reserve-supersede-001',
            'employee_id' => $reserveEmployeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => '2026-09-01',
            'stay_end' => '2026-09-30',
        ]),
    );

    expect($result->disposition)->toBe(ReservePromotionDisposition::Issued);
    expect($result->priorWinnerVoucher?->lifecycleState)->toBe(VoucherLifecycleState::Superseded);

    $storedWinner = VoucherModel::query()->find($winner->requireId()->value);
    expect($storedWinner?->lifecycle_state)->toBe(VoucherLifecycleState::Superseded);

    $transitions = app(VoucherLifecycleTransitionRepositoryContract::class)
        ->findByVoucherId($winner->requireId());

    expect(collect($transitions)->contains(
        fn ($transition) => $transition->toState === VoucherLifecycleState::Superseded
            && $transition->fromState === VoucherLifecycleState::Issued,
    ))->toBeTrue();
});

it('evaluates and issues voucher for next eligible reserve', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $winnerEmployeeId = UuidGenerator::uuid7();
    $reserveEmployeeId = UuidGenerator::uuid7();
    $winner = issueExternalLotteryWinner('winner-issue-reserve-001', $winnerEmployeeId, $dormitoryId);

    $result = app(ReservePromotionPathContract::class)->processPromotion(
        reservePromotionFacts('promo-issue-reserve-001', 'prog-issue-reserve', $winner, [
            'correlation_id' => 'reserve-issue-001',
            'employee_id' => $reserveEmployeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => '2026-09-01',
            'stay_end' => '2026-09-30',
        ]),
    );

    expect($result->disposition)->toBe(ReservePromotionDisposition::Issued);
    expect($result->reserveVoucher)->not->toBeNull();
    expect($result->reserveVoucher?->lifecycleState)->toBe(VoucherLifecycleState::Issued);
    expect($result->reserveVoucher?->employeeId)->toBe($reserveEmployeeId);
    expect($result->reserveVoucher?->correlationId->value)->toBe('reserve-issue-001');
});

it('completes promotion with no issuance outcome when no eligible reserves remain', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $winnerEmployeeId = UuidGenerator::uuid7();
    $winner = issueExternalLotteryWinner('winner-no-reserve-001', $winnerEmployeeId, $dormitoryId);

    $result = app(ReservePromotionPathContract::class)->processPromotion(
        reservePromotionFacts('promo-no-reserve-001', 'prog-no-reserve', $winner),
    );

    expect($result->disposition)->toBe(ReservePromotionDisposition::NoEligibleReserves);
    expect($result->reserveVoucher)->toBeNull();
    expect(VoucherModel::query()->where('employee_id', $winnerEmployeeId)->count())->toBe(1);

    $transitions = app(VoucherLifecycleTransitionRepositoryContract::class)
        ->findByVoucherId($winner->requireId());

    expect(collect($transitions)->contains(
        fn ($transition) => ($transition->payload['promotion_outcome'] ?? null) === 'no_issuance'
            && ($transition->payload['event_type'] ?? null) === 'reserve_promotion_outcome',
    ))->toBeTrue();
});

it('records no issuance outcome when reserve is ineligible', function (): void {
    $externalDormitoryId = UuidGenerator::uuid7();
    $internalDormitoryId = UuidGenerator::uuid7();
    $winnerEmployeeId = UuidGenerator::uuid7();
    $reserveEmployeeId = UuidGenerator::uuid7();

    bindReservePromotionClassifications([
        $externalDormitoryId => AccommodationClassification::External,
        $internalDormitoryId => AccommodationClassification::Internal,
    ]);

    $winner = issueExternalLotteryWinner('winner-ineligible-reserve-001', $winnerEmployeeId, $externalDormitoryId);

    bindReservePromotionClassifications([
        $externalDormitoryId => AccommodationClassification::External,
        $internalDormitoryId => AccommodationClassification::Internal,
    ]);
    app()->forgetInstance(EvaluateVoucherEligibilityAction::class);
    app()->forgetInstance(VoucherEligibilityEvaluationContract::class);

    $result = app(ReservePromotionPathContract::class)->processPromotion(
        reservePromotionFacts('promo-ineligible-reserve-001', 'prog-ineligible-reserve', $winner, [
            'correlation_id' => 'reserve-ineligible-001',
            'employee_id' => $reserveEmployeeId,
            'dormitory_id' => $internalDormitoryId,
            'stay_start' => '2026-09-01',
            'stay_end' => '2026-09-30',
        ]),
    );

    expect($result->disposition)->toBe(ReservePromotionDisposition::ReserveIneligible);
    expect($result->reserveVoucher)->toBeNull();
    expect(VoucherModel::query()->count())->toBe(1);

    $transitions = app(VoucherLifecycleTransitionRepositoryContract::class)
        ->findByVoucherId($winner->requireId());

    expect(collect($transitions)->contains(
        fn ($transition) => ($transition->payload['promotion_outcome'] ?? null) === 'no_issuance',
    ))->toBeTrue();
});

it('ignores reserve promotion facts for internal dormitory programs', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $winnerEmployeeId = UuidGenerator::uuid7();
    $reserveEmployeeId = UuidGenerator::uuid7();
    $winner = issueExternalLotteryWinner('winner-internal-promo-001', $winnerEmployeeId, $dormitoryId);

    $result = app(ReservePromotionPathContract::class)->processPromotion(
        reservePromotionFacts('promo-internal-001', 'prog-internal', $winner, [
            'correlation_id' => 'reserve-internal-001',
            'employee_id' => $reserveEmployeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => '2026-09-01',
            'stay_end' => '2026-09-30',
        ], programType: 'internal'),
    );

    expect($result->disposition)->toBe(ReservePromotionDisposition::IgnoredInternalProgram);
    expect(VoucherIssuanceTriggerModel::query()->where('correlation_id', 'promo-internal-001')->exists())->toBeFalse();
    expect(VoucherModel::query()->count())->toBe(1);
});

it('rejects duplicate reserve promotion correlation identifiers', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $winnerEmployeeId = UuidGenerator::uuid7();
    $reserveEmployeeId = UuidGenerator::uuid7();
    $winner = issueExternalLotteryWinner('winner-dup-promo-001', $winnerEmployeeId, $dormitoryId);

    $facts = reservePromotionFacts('promo-duplicate-001', 'prog-duplicate', $winner, [
        'correlation_id' => 'reserve-duplicate-001',
        'employee_id' => $reserveEmployeeId,
        'dormitory_id' => $dormitoryId,
        'stay_start' => '2026-09-01',
        'stay_end' => '2026-09-30',
    ]);

    app(ReservePromotionPathContract::class)->processPromotion($facts);
    $second = app(ReservePromotionPathContract::class)->processPromotion($facts);

    expect($second->disposition)->toBe(ReservePromotionDisposition::DuplicateRejected);
    expect(VoucherModel::query()->count())->toBe(2);
});
