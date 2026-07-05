<?php

declare(strict_types=1);

use App\Modules\Voucher\Application\Contracts\AccommodationClassificationReadPort;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityEvaluationContract;
use App\Modules\Voucher\Application\Contracts\VoucherIssuanceContract;
use App\Modules\Voucher\Application\Contracts\VoucherLifecycleContract;
use App\Modules\Voucher\Application\Contracts\VoucherReadContract;
use App\Modules\Voucher\Application\Contracts\VoucherReadRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\DTOs\InboundTriggerFactsDto;
use App\Modules\Voucher\Application\Services\VoucherReadService;
use App\Modules\Voucher\Domain\Enums\AccommodationClassification;
use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Infrastructure\Adapters\InMemoryAccommodationClassificationReadAdapter;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\Exceptions\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function issueVoucherForReadAccess(
    string $correlationId,
    string $employeeId,
    string $dormitoryId,
    string $stayStart = '2026-09-01',
    string $stayEnd = '2026-09-30',
): Voucher {
    app()->instance(
        AccommodationClassificationReadPort::class,
        new InMemoryAccommodationClassificationReadAdapter([
            $dormitoryId => AccommodationClassification::External,
        ]),
    );

    $trigger = app(VoucherTriggerIntakeContract::class)->accept(
        InboundTriggerFactsDto::fromLotteryFacts([
            'correlation_id' => $correlationId,
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'stay_start' => $stayStart,
            'stay_end' => $stayEnd,
        ]),
    );
    $eligibility = app(VoucherEligibilityEvaluationContract::class)->evaluateForTrigger($trigger->requireId());

    return app(VoucherIssuanceContract::class)->issueFromEligibility($eligibility->requireId());
}

it('allows employee to view active and historical vouchers with required metadata', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $active = issueVoucherForReadAccess('read-active-001', $employeeId, $dormitoryId, '2026-09-01', '2026-09-30');
    $historical = issueVoucherForReadAccess('read-historical-001', $employeeId, $dormitoryId, '2026-11-01', '2026-11-30');

    app(VoucherLifecycleContract::class)->expire(
        $historical->requireId(),
        new DateTimeImmutable('2026-12-01', new DateTimeZone('UTC')),
    );

    $projections = app(VoucherReadContract::class)->listForEmployee($employeeId);

    expect($projections)->toHaveCount(2);

    $activeProjections = array_values(array_filter(
        $projections,
        static fn ($projection): bool => $projection->validityStart === '2026-09-01',
    ));

    expect($activeProjections)->toHaveCount(1);

    $activeProjection = $activeProjections[0];
    expect($activeProjection->employeeId)->toBe($employeeId);
    expect($activeProjection->code)->toMatch('/^[A-F0-9]{32}$/');
    expect($activeProjection->dormitoryId)->toBe($dormitoryId);
    expect($activeProjection->lifecycleState)->toBe(VoucherLifecycleState::Issued->value);
    expect($activeProjection->validityStart)->toBe('2026-09-01');
    expect($activeProjection->validityEnd)->toBe('2026-09-30');
});

it('filters employee vouchers by lifecycle state', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $active = issueVoucherForReadAccess('read-filter-active-001', $employeeId, $dormitoryId, '2026-09-01', '2026-09-30');
    $historical = issueVoucherForReadAccess('read-filter-expired-001', $employeeId, $dormitoryId, '2026-11-01', '2026-11-30');

    app(VoucherLifecycleContract::class)->expire(
        $historical->requireId(),
        new DateTimeImmutable('2026-12-01', new DateTimeZone('UTC')),
    );

    $issuedOnly = app(VoucherReadContract::class)->listForEmployee($employeeId, 'issued');

    expect($issuedOnly)->toHaveCount(1);
    expect($issuedOnly[0]->lifecycleState)->toBe(VoucherLifecycleState::Issued->value);
    expect($issuedOnly[0]->voucherId)->toBe($active->requireId()->value);
});

it('retrieves voucher projection by id for authorized consumers', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $voucher = issueVoucherForReadAccess('read-by-id-001', $employeeId, $dormitoryId);

    $projection = app(VoucherReadContract::class)->getById($voucher->requireId()->value);

    expect($projection)->not->toBeNull();
    expect($projection?->voucherId)->toBe($voucher->requireId()->value);
    expect($projection?->code)->toBe($voucher->code->value);
    expect($projection?->upstreamSource)->toBe(TriggerSource::Lottery->value);
});

it('allows authorized operator to search voucher by code for verification', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $voucher = issueVoucherForReadAccess('read-by-code-001', $employeeId, $dormitoryId);

    $projection = app(VoucherReadContract::class)->findByCode($voucher->code->value);

    expect($projection)->not->toBeNull();
    expect($projection?->voucherId)->toBe($voucher->requireId()->value);
    expect($projection?->employeeId)->toBe($employeeId);
    expect($projection?->dormitoryId)->toBe($dormitoryId);
    expect($projection?->lifecycleState)->toBe(VoucherLifecycleState::Issued->value);
});

it('returns null when voucher code is not found', function (): void {
    expect(app(VoucherReadContract::class)->findByCode(str_repeat('B', 32)))->toBeNull();
});

it('does not mutate voucher lifecycle state during inquiry paths', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();
    $voucher = issueVoucherForReadAccess('read-no-mutation-001', $employeeId, $dormitoryId);
    $voucherId = $voucher->requireId()->value;

    $before = VoucherModel::query()->findOrFail($voucherId);

    app(VoucherReadContract::class)->getById($voucherId);
    app(VoucherReadContract::class)->findByCode($voucher->code->value);
    app(VoucherReadContract::class)->listForEmployee($employeeId);
    app(VoucherReadContract::class)->listForEmployee($employeeId, 'issued');

    $after = VoucherModel::query()->findOrFail($voucherId);

    expect($after->lifecycle_state)->toBe($before->lifecycle_state);
    expect($after->code)->toBe($before->code);
    if ($after->updated_at === null || $before->updated_at === null) {
        throw new UnexpectedValueException('Expected voucher timestamps.');
    }

    expect($after->updated_at->equalTo($before->updated_at))->toBeTrue();
});

it('rejects invalid lifecycle state filters', function (): void {
    expect(fn () => app(VoucherReadContract::class)->listForEmployee(UuidGenerator::uuid7(), 'not-a-state'))
        ->toThrow(ValidationException::class);
});

it('uses a read-only service without lifecycle or issuance command dependencies', function (): void {
    $service = app(VoucherReadService::class);
    $reflection = new ReflectionClass($service);
    $constructor = $reflection->getConstructor();
    $parameters = $constructor?->getParameters() ?? [];

    expect($parameters)->toHaveCount(1);

    $parameterType = $parameters[0]->getType();
    if (! $parameterType instanceof ReflectionNamedType) {
        throw new UnexpectedValueException('Expected voucher read repository parameter type.');
    }

    expect($parameterType->getName())->toBe(VoucherReadRepositoryContract::class);
});
