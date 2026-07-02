<?php

declare(strict_types=1);

use App\Modules\Voucher\Application\Contracts\AccommodationClassificationReadPort;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityEvaluationContract;
use App\Modules\Voucher\Application\Contracts\VoucherIssuanceContract;
use App\Modules\Voucher\Application\Contracts\VoucherLifecycleContract;
use App\Modules\Voucher\Application\Contracts\VoucherLifecycleTransitionRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\DTOs\InboundTriggerFactsDto;
use App\Modules\Voucher\Domain\Enums\AccommodationClassification;
use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Domain\Exceptions\VoucherReissuanceRejectedException;
use App\Modules\Voucher\Domain\ValueObjects\VoucherCode;
use App\Modules\Voucher\Infrastructure\Adapters\InMemoryAccommodationClassificationReadAdapter;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function issueEligibleVoucher(
    string $correlationId,
    string $employeeId,
    string $dormitoryId,
    string $stayStart = '2026-09-01',
    string $stayEnd = '2026-09-30',
    TriggerSource $source = TriggerSource::Lottery,
): object {
    app()->instance(
        AccommodationClassificationReadPort::class,
        new InMemoryAccommodationClassificationReadAdapter([
            $dormitoryId => AccommodationClassification::External,
        ]),
    );

    $facts = $source === TriggerSource::Lottery
        ? InboundTriggerFactsDto::fromLotteryFacts([
            'correlation_id' => $correlationId,
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => $stayStart,
            'stay_end' => $stayEnd,
        ])
        : InboundTriggerFactsDto::fromAllocationFacts([
            'correlation_id' => $correlationId,
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => $stayStart,
            'stay_end' => $stayEnd,
            'allocation_trigger_reason' => 'unfulfilled_accommodation',
        ]);

    $trigger = app(VoucherTriggerIntakeContract::class)->accept($facts);
    $eligibility = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    return app(VoucherIssuanceContract::class)->issueFromEligibility($eligibility->requireId());
}

it('issues a unique voucher code in issued state from eligible evaluation', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();

    $voucher = issueEligibleVoucher('issue-001', $employeeId, $dormitoryId);

    expect($voucher->lifecycleState)->toBe(VoucherLifecycleState::Issued);
    expect($voucher->code->value)->toMatch('/^[A-F0-9]{32}$/');
    expect($voucher->employeeId)->toBe($employeeId);
    expect($voucher->dormitoryId)->toBe($dormitoryId);
    expect($voucher->upstreamSource)->toBe(TriggerSource::Lottery);
});

it('regenerates voucher codes until global uniqueness is confirmed', function (): void {
    $existingCode = VoucherCode::fromString(str_repeat('A', 32));
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();

    VoucherModel::query()->create([
        'eligibility_outcome_id' => UuidGenerator::uuid7(),
        'trigger_id' => UuidGenerator::uuid7(),
        'correlation_id' => 'preexisting-code',
        'employee_id' => $employeeId,
        'dormitory_id' => $dormitoryId,
        'upstream_source' => TriggerSource::Lottery->value,
        'code' => $existingCode->value,
        'lifecycle_state' => VoucherLifecycleState::Issued->value,
        'stay_period' => '[2026-09-01,2026-09-30]',
        'validity_start' => '2026-09-01 00:00:00+00',
        'validity_end' => '2026-09-30 00:00:00+00',
        'issued_at' => now(),
    ]);

    $attempts = 0;
    app()->bind(\App\Modules\Voucher\Domain\Services\VoucherCodeGenerator::class, function () use ($existingCode, &$attempts) {
        return new class($existingCode, $attempts) extends \App\Modules\Voucher\Domain\Services\VoucherCodeGenerator
        {
            public function __construct(
                private readonly VoucherCode $duplicate,
                private int &$attempts,
            ) {}

            public function generate(): VoucherCode
            {
                $this->attempts++;

                return $this->attempts === 1 ? $this->duplicate : parent::generate();
            }
        };
    });

    app()->forgetInstance(\App\Modules\Voucher\Application\Services\IssueVoucherAction::class);
    app()->forgetInstance(VoucherIssuanceContract::class);

    $voucher = issueEligibleVoucher('issue-unique-001', $employeeId, $dormitoryId);

    expect($attempts)->toBeGreaterThan(1);
    expect($voucher->code->value)->not->toBe($existingCode->value);
});

it('attaches employee dormitory stay period and upstream source to issued voucher', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = UuidGenerator::uuid7();

    app()->instance(
        AccommodationClassificationReadPort::class,
        new InMemoryAccommodationClassificationReadAdapter([
            $dormitoryId => AccommodationClassification::External,
        ]),
    );

    $trigger = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromAllocationFacts([
            'correlation_id' => 'issue-metadata-001',
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'request_id' => $requestId,
            'stay_start' => '2026-10-01',
            'stay_end' => '2026-10-31',
            'allocation_trigger_reason' => 'unfulfilled_accommodation',
        ]),
    );
    $eligibility = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());
    $voucher = app(VoucherIssuanceContract::class)->issueFromEligibility($eligibility->requireId());

    expect($voucher->employeeId)->toBe($employeeId);
    expect($voucher->dormitoryId)->toBe($dormitoryId);
    expect($voucher->requestId)->toBe($requestId);
    expect($voucher->upstreamSource)->toBe(TriggerSource::Allocation);
    expect($voucher->stayPeriod->start->format('Y-m-d'))->toBe('2026-10-01');
    expect($voucher->stayPeriod->end->format('Y-m-d'))->toBe('2026-10-31');
});

it('transitions issued voucher to expired when validity window ends', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $voucher = issueEligibleVoucher('expire-001', $employeeId, $dormitoryId, '2026-08-01', '2026-08-31');

    $expired = app(VoucherLifecycleContract::class)->expire(
        $voucher->requireId(),
        new \DateTimeImmutable('2026-09-01', new \DateTimeZone('UTC')),
    );

    expect($expired->lifecycleState)->toBe(VoucherLifecycleState::Expired);
});

it('rejects re-issuance from terminal voucher without new eligible evaluation', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $voucher = issueEligibleVoucher('reissue-001', $employeeId, $dormitoryId, '2026-08-01', '2026-08-31');

    app(VoucherLifecycleContract::class)->expire(
        $voucher->requireId(),
        new \DateTimeImmutable('2026-09-01', new \DateTimeZone('UTC')),
    );

    expect(fn () => app(VoucherIssuanceContract::class)->issueFromEligibility($voucher->eligibilityOutcomeId))
        ->toThrow(VoucherReissuanceRejectedException::class);
});

it('archives issued voucher records without silent deletion', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $voucher = issueEligibleVoucher('archive-001', $employeeId, $dormitoryId);

    $archived = app(VoucherLifecycleContract::class)->archive(
        $voucher->requireId(),
        new \DateTimeImmutable('2026-09-15', new \DateTimeZone('UTC')),
    );

    expect($archived->archivedAt)->not->toBeNull();
    expect(VoucherModel::query()->find($voucher->requireId()->value))->not->toBeNull();
});

it('records material lifecycle transitions for downstream consumers', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $voucher = issueEligibleVoucher('transition-001', $employeeId, $dormitoryId, '2026-08-01', '2026-08-31');

    app(VoucherLifecycleContract::class)->expire(
        $voucher->requireId(),
        new \DateTimeImmutable('2026-09-01', new \DateTimeZone('UTC')),
    );

    $transitions = app(VoucherLifecycleTransitionRepositoryContract::class)
        ->findByVoucherId($voucher->requireId());

    expect($transitions)->toHaveCount(2);
    expect($transitions[0]->toState)->toBe(VoucherLifecycleState::Issued);
    expect($transitions[1]->toState)->toBe(VoucherLifecycleState::Expired);
    expect($transitions[0]->payload)->toHaveKeys(['voucher_id', 'employee_id', 'code', 'correlation_id', 'to_state']);
    expect($transitions[1]->payload['from_state'])->toBe('issued');
    expect($transitions[1]->payload['to_state'])->toBe('expired');
});

it('runs voucher issuance lifecycle migrations', function (): void {
    expect(Schema::hasTable('vouchers'))->toBeTrue();
    expect(Schema::hasTable('voucher_lifecycle_transitions'))->toBeTrue();
});
