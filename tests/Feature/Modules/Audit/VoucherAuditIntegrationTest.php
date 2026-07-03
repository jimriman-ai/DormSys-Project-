<?php

declare(strict_types=1);

use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Modules\Voucher\Application\Contracts\AccommodationClassificationReadPort;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityEvaluationContract;
use App\Modules\Voucher\Application\Contracts\VoucherIssuanceContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\DTOs\InboundTriggerFactsDto;
use App\Modules\Voucher\Domain\Enums\AccommodationClassification;
use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Infrastructure\Adapters\InMemoryAccommodationClassificationReadAdapter;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
});

function issueVoucherForAuditTest(string $correlationId, TriggerSource $source = TriggerSource::Lottery): object
{
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();

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
            'stay_start' => '2026-09-01',
            'stay_end' => '2026-09-30',
        ])
        : InboundTriggerFactsDto::fromAllocationFacts([
            'correlation_id' => $correlationId,
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => '2026-09-01',
            'stay_end' => '2026-09-30',
            'allocation_trigger_reason' => 'unfulfilled_accommodation',
        ]);

    $trigger = app(VoucherTriggerIntakeContract::class)->accept($facts);
    $eligibility = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    return app(VoucherIssuanceContract::class)->issueFromEligibility($eligibility->requireId());
}

it('records voucher issued audit entry from lifecycle transition adapter', function (): void {
    $voucher = issueVoucherForAuditTest('voucher-audit-001', TriggerSource::Lottery);
    $voucherId = $voucher->requireId()->value;

    $entry = AuditLogModel::query()
        ->where('entity_type', 'voucher')
        ->where('entity_id', $voucherId)
        ->where('event_type', AuditEventType::VoucherIssued)
        ->first();

    expect($entry)->not->toBeNull();
    expect($entry->source_context)->toBe('voucher');
    expect($entry->actor_type)->toBe(ActorType::System);
    expect($entry->actor_id)->toBe('system:lottery_draw');
    expect($entry->correlation_id)->toBe('voucher:voucher:'.$voucherId.':voucher.issued:issued');
    expect($entry->new_values)->toBe(['lifecycle_state' => VoucherLifecycleState::Issued->value]);
    expect($entry->metadata['employee_id'])->toBe($voucher->employeeId);
    expect($entry->metadata['upstream_source'])->toBe(TriggerSource::Lottery->value);
});

it('maps allocation upstream source to reserve promotion system actor', function (): void {
    $voucher = issueVoucherForAuditTest('voucher-audit-allocation-001', TriggerSource::Allocation);
    $voucherId = $voucher->requireId()->value;

    $entry = AuditLogModel::query()
        ->where('entity_id', $voucherId)
        ->where('event_type', AuditEventType::VoucherIssued)
        ->first();

    expect($entry)->not->toBeNull();
    expect($entry->actor_id)->toBe('system:reserve_promotion');
    expect($entry->metadata['upstream_source'])->toBe(TriggerSource::Allocation->value);
});
