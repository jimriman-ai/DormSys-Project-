<?php

declare(strict_types=1);

use App\Modules\Voucher\Application\Contracts\AccommodationClassificationReadPort;
use App\Modules\Voucher\Application\Contracts\ExternalLotteryWinnerPathContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerRepositoryContract;
use App\Modules\Voucher\Application\DTOs\ExternalLotteryWinnerBatchDto;
use App\Modules\Voucher\Domain\Enums\AccommodationClassification;
use App\Modules\Voucher\Domain\Enums\EligibilityOutcome;
use App\Modules\Voucher\Domain\Enums\ExternalLotteryBatchDisposition;
use App\Modules\Voucher\Domain\Enums\ExternalLotteryWinnerDisposition;
use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Infrastructure\Adapters\InMemoryAccommodationClassificationReadAdapter;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherIssuanceTriggerModel;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\Exceptions\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function bindExternalLotteryClassifications(array $classifications): void
{
    app()->instance(
        AccommodationClassificationReadPort::class,
        new InMemoryAccommodationClassificationReadAdapter($classifications),
    );
}

function externalLotteryWinnerFacts(
    string $correlationId,
    string $employeeId,
    string $dormitoryId,
    array $extra = [],
): array {
    return array_merge([
        'correlation_id' => $correlationId,
        'employee_id' => $employeeId,
        'dormitory_id' => $dormitoryId,
        'stay_start' => '2026-09-01',
        'stay_end' => '2026-09-30',
    ], $extra);
}

function externalLotteryBatch(
    string $programId,
    array $winners,
    int $capacity = 10,
    string $programType = 'external',
    bool $drawCompleted = true,
): ExternalLotteryWinnerBatchDto {
    return ExternalLotteryWinnerBatchDto::fromUpstreamFacts([
        'program_id' => $programId,
        'program_type' => $programType,
        'draw_completed' => $drawCompleted,
        'program_capacity' => $capacity,
        'winners' => $winners,
    ]);
}

it('processes external lottery winner facts after draw completion end to end', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $employeeId = UuidGenerator::uuid7();
    bindExternalLotteryClassifications([$dormitoryId => AccommodationClassification::External]);

    $result = app(ExternalLotteryWinnerPathContract::class)->processWinnerBatch(
        externalLotteryBatch('prog-external-001', [
            externalLotteryWinnerFacts('lottery-winner-001', $employeeId, $dormitoryId, [
                'lottery_result_id' => UuidGenerator::uuid7(),
            ]),
        ]),
    );

    expect($result->batchDisposition)->toBe(ExternalLotteryBatchDisposition::Processed);
    expect($result->issuedCount)->toBe(1);
    expect($result->winnerResults)->toHaveCount(1);
    expect($result->winnerResults[0]->disposition)->toBe(ExternalLotteryWinnerDisposition::Issued);
    expect($result->winnerResults[0]->voucher?->lifecycleState)->toBe(VoucherLifecycleState::Issued);
    expect($result->winnerResults[0]->voucher?->upstreamSource)->toBe(TriggerSource::Lottery);
});

it('rejects processing when draw is not completed', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $employeeId = UuidGenerator::uuid7();
    bindExternalLotteryClassifications([$dormitoryId => AccommodationClassification::External]);

    expect(fn () => app(ExternalLotteryWinnerPathContract::class)->processWinnerBatch(
        externalLotteryBatch('prog-incomplete-001', [
            externalLotteryWinnerFacts('lottery-incomplete-001', $employeeId, $dormitoryId),
        ], drawCompleted: false),
    ))->toThrow(ValidationException::class);
});

it('issues vouchers for winners up to program capacity', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $winners = [];

    for ($index = 1; $index <= 4; $index++) {
        $winners[] = externalLotteryWinnerFacts(
            "lottery-capacity-{$index}",
            UuidGenerator::uuid7(),
            $dormitoryId,
        );
    }

    bindExternalLotteryClassifications([$dormitoryId => AccommodationClassification::External]);

    $result = app(ExternalLotteryWinnerPathContract::class)->processWinnerBatch(
        externalLotteryBatch('prog-capacity-001', $winners, capacity: 2),
    );

    expect($result->issuedCount)->toBe(2);
    expect($result->winnerResults[0]->disposition)->toBe(ExternalLotteryWinnerDisposition::Issued);
    expect($result->winnerResults[1]->disposition)->toBe(ExternalLotteryWinnerDisposition::Issued);
    expect($result->winnerResults[2]->disposition)->toBe(ExternalLotteryWinnerDisposition::SkippedCapacity);
    expect($result->winnerResults[3]->disposition)->toBe(ExternalLotteryWinnerDisposition::SkippedCapacity);
    expect(VoucherModel::query()->count())->toBe(2);
});

it('does not store room or bed identifiers from lottery winner facts', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $employeeId = UuidGenerator::uuid7();
    $roomId = UuidGenerator::uuid7();
    $bedId = UuidGenerator::uuid7();
    bindExternalLotteryClassifications([$dormitoryId => AccommodationClassification::External]);

    app(ExternalLotteryWinnerPathContract::class)->processWinnerBatch(
        externalLotteryBatch('prog-sanitize-001', [
            externalLotteryWinnerFacts('lottery-sanitize-001', $employeeId, $dormitoryId, [
                'room_id' => $roomId,
                'bed_id' => $bedId,
                'room_number' => '101',
                'bed_number' => 'A',
            ]),
        ]),
    );

    $trigger = VoucherIssuanceTriggerModel::query()
        ->where('correlation_id', 'lottery-sanitize-001')
        ->first();

    expect($trigger)->not->toBeNull();
    expect($trigger->upstream_facts)->not->toHaveKey('room_id');
    expect($trigger->upstream_facts)->not->toHaveKey('bed_id');
    expect($trigger->upstream_facts)->not->toHaveKey('room_number');
    expect($trigger->upstream_facts)->not->toHaveKey('bed_number');
    expect($trigger->upstream_facts)->toHaveKey('draw_completed');
    expect($trigger->upstream_facts)->toHaveKey('program_id');

    $voucher = VoucherModel::query()->where('correlation_id', 'lottery-sanitize-001')->first();
    expect($voucher)->not->toBeNull();
    expect($voucher->dormitory_id)->toBe($dormitoryId);
});

it('ignores trigger facts for internal dormitory programs without issuance', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $employeeId = UuidGenerator::uuid7();
    bindExternalLotteryClassifications([$dormitoryId => AccommodationClassification::External]);

    $result = app(ExternalLotteryWinnerPathContract::class)->processWinnerBatch(
        externalLotteryBatch('prog-internal-001', [
            externalLotteryWinnerFacts('lottery-internal-program-001', $employeeId, $dormitoryId),
        ], programType: 'internal'),
    );

    expect($result->batchDisposition)->toBe(ExternalLotteryBatchDisposition::IgnoredInternalProgram);
    expect($result->issuedCount)->toBe(0);
    expect($result->winnerResults)->toBe([]);
    expect(VoucherIssuanceTriggerModel::query()->count())->toBe(0);
    expect(VoucherModel::query()->count())->toBe(0);
});

it('evaluates ineligible winners without issuing vouchers', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $employeeId = UuidGenerator::uuid7();
    bindExternalLotteryClassifications([$dormitoryId => AccommodationClassification::Internal]);

    $result = app(ExternalLotteryWinnerPathContract::class)->processWinnerBatch(
        externalLotteryBatch('prog-ineligible-001', [
            externalLotteryWinnerFacts('lottery-ineligible-001', $employeeId, $dormitoryId),
        ]),
    );

    expect($result->issuedCount)->toBe(0);
    expect($result->winnerResults[0]->disposition)->toBe(ExternalLotteryWinnerDisposition::NotEligible);
    expect($result->winnerResults[0]->eligibilityOutcome?->outcome)->toBe(EligibilityOutcome::Ineligible);
    expect(VoucherModel::query()->count())->toBe(0);
});

it('rejects duplicate winner correlation identifiers without duplicate issuance', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $employeeId = UuidGenerator::uuid7();
    bindExternalLotteryClassifications([$dormitoryId => AccommodationClassification::External]);

    $batch = externalLotteryBatch('prog-duplicate-001', [
        externalLotteryWinnerFacts('lottery-duplicate-001', $employeeId, $dormitoryId),
    ]);

    $first = app(ExternalLotteryWinnerPathContract::class)->processWinnerBatch($batch);
    $second = app(ExternalLotteryWinnerPathContract::class)->processWinnerBatch($batch);

    expect($first->issuedCount)->toBe(1);
    expect($second->winnerResults[0]->disposition)->toBe(ExternalLotteryWinnerDisposition::DuplicateRejected);
    expect(VoucherModel::query()->where('correlation_id', 'lottery-duplicate-001')->count())->toBe(1);
});

it('marks issuance path completed for processed lottery winners', function (): void {
    $dormitoryId = UuidGenerator::uuid7();
    $employeeId = UuidGenerator::uuid7();
    bindExternalLotteryClassifications([$dormitoryId => AccommodationClassification::External]);

    app(ExternalLotteryWinnerPathContract::class)->processWinnerBatch(
        externalLotteryBatch('prog-complete-001', [
            externalLotteryWinnerFacts('lottery-complete-001', $employeeId, $dormitoryId),
        ]),
    );

    $trigger = app(VoucherTriggerRepositoryContract::class)->findByCorrelationId(
        CorrelationId::fromString('lottery-complete-001'),
    );

    expect($trigger?->hasCompletedIssuancePath())->toBeTrue();
});
