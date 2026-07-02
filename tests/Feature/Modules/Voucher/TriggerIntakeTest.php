<?php

declare(strict_types=1);

use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerRepositoryContract;
use App\Modules\Voucher\Application\DTOs\InboundTriggerFactsDto;
use App\Modules\Voucher\Domain\Enums\TriggerIntakeStatus;
use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Modules\Voucher\Domain\Exceptions\DuplicateTriggerCorrelationException;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('accepts lottery trigger facts without assuming upstream ownership', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();

    $trigger = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromLotteryFacts([
            'correlation_id' => 'lottery-draw-001',
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => '2026-09-01',
            'stay_end' => '2026-09-30',
            'lottery_result_id' => UuidGenerator::uuid7(),
        ]),
    );

    expect($trigger->source)->toBe(TriggerSource::Lottery);
    expect($trigger->employeeId)->toBe($employeeId);
    expect($trigger->correlationId->value)->toBe('lottery-draw-001');
    expect($trigger->upstreamFacts)->toHaveKey('lottery_result_id');
    expect($trigger->status)->toBe(TriggerIntakeStatus::Accepted);
});

it('accepts allocation trigger facts without assuming upstream ownership', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $requestId = UuidGenerator::uuid7();

    $trigger = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromAllocationFacts([
            'correlation_id' => 'allocation-unfulfilled-001',
            'employee_id' => $employeeId,
            'request_id' => $requestId,
            'stay_start' => '2026-10-01',
            'stay_end' => '2026-10-31',
            'allocation_trigger_reason' => 'unfulfilled_accommodation',
        ]),
    );

    expect($trigger->source)->toBe(TriggerSource::Allocation);
    expect($trigger->requestId)->toBe($requestId);
    expect($trigger->upstreamFacts)->toHaveKey('allocation_trigger_reason');
});

it('records correlation identifier on accepted triggers', function (): void {
    $facts = InboundTriggerFactsDto::fromLotteryFacts([
        'correlation_id' => 'corr-trace-42',
        'employee_id' => UuidGenerator::uuid7(),
        'stay_start' => '2026-11-01',
        'stay_end' => '2026-11-30',
    ]);

    $trigger = app(VoucherTriggerIntakeContract::class)->accept($facts);
    $stored = app(VoucherTriggerRepositoryContract::class)->findByCorrelationId($facts->correlationId);

    expect($stored)->not->toBeNull();
    expect($stored?->correlationId->value)->toBe('corr-trace-42');
    expect($trigger->id)->not->toBeNull();
});

it('rejects duplicate correlation identifiers', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $facts = InboundTriggerFactsDto::fromLotteryFacts([
        'correlation_id' => 'duplicate-corr-001',
        'employee_id' => $employeeId,
        'stay_start' => '2026-12-01',
        'stay_end' => '2026-12-31',
    ]);

    app(VoucherTriggerIntakeContract::class)->accept($facts);

    expect(fn () => app(VoucherTriggerIntakeContract::class)->accept($facts))
        ->toThrow(DuplicateTriggerCorrelationException::class);
});

it('rejects duplicate correlation after issuance path completion', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $facts = InboundTriggerFactsDto::fromLotteryFacts([
        'correlation_id' => 'completed-path-001',
        'employee_id' => $employeeId,
        'stay_start' => '2027-01-01',
        'stay_end' => '2027-01-31',
    ]);

    $trigger = app(VoucherTriggerIntakeContract::class)->accept($facts);
    app(VoucherTriggerRepositoryContract::class)->markIssuancePathCompleted($trigger->requireId());

    expect(fn () => app(VoucherTriggerIntakeContract::class)->accept($facts))
        ->toThrow(DuplicateTriggerCorrelationException::class);
});

it('supersedes overlapping active triggers for the same employee', function (): void {
    $employeeId = UuidGenerator::uuid7();

    $first = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromLotteryFacts([
            'correlation_id' => 'overlap-first',
            'employee_id' => $employeeId,
            'stay_start' => '2027-02-01',
            'stay_end' => '2027-02-28',
        ]),
    );

    $second = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromAllocationFacts([
            'correlation_id' => 'overlap-second',
            'employee_id' => $employeeId,
            'stay_start' => '2027-02-15',
            'stay_end' => '2027-03-15',
        ]),
    );

    $storedFirst = app(VoucherTriggerRepositoryContract::class)->findByCorrelationId(
        $first->correlationId,
    );

    expect($storedFirst?->status)->toBe(TriggerIntakeStatus::Superseded);
    expect($storedFirst?->supersededByTriggerId?->value)->toBe($second->requireId()->value);
    expect($second->status)->toBe(TriggerIntakeStatus::Accepted);
});

it('allows non-overlapping triggers for the same employee', function (): void {
    $employeeId = UuidGenerator::uuid7();

    $first = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromLotteryFacts([
            'correlation_id' => 'non-overlap-first',
            'employee_id' => $employeeId,
            'stay_start' => '2027-04-01',
            'stay_end' => '2027-04-15',
        ]),
    );

    $second = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromLotteryFacts([
            'correlation_id' => 'non-overlap-second',
            'employee_id' => $employeeId,
            'stay_start' => '2027-04-16',
            'stay_end' => '2027-04-30',
        ]),
    );

    expect($first->status)->toBe(TriggerIntakeStatus::Accepted);
    expect($second->status)->toBe(TriggerIntakeStatus::Accepted);
});

it('runs voucher module migrations', function (): void {
    expect(Schema::hasTable('voucher_issuance_triggers'))->toBeTrue();
});
