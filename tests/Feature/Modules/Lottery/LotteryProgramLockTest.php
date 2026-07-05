<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Contracts\LotteryEligibleSnapshotRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Application\Services\LotteryScoringConfigReader;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Lottery\Domain\Models\EligibleSnapshot;
use App\Modules\Lottery\Domain\Services\LotteryScoringEngine;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

require_once __DIR__.'/LotteryRegistrationEnrollmentTest.php';
require_once __DIR__.'/LotteryFeatureSupport.php';

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-30 12:00:00');

    if (! Schema::hasTable('settings')) {
        Schema::create('settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->json('value');
            $table->timestamps();
        });
    }

    DB::table('settings')->updateOrInsert(
        ['key' => LotteryScoringConfigReader::SETTINGS_KEY],
        [
            'id' => UuidGenerator::uuid7(),
            'value' => json_encode([
                'version' => '1.0.0',
                'base_score_coefficient' => 1.0,
                'department_priority_coefficient' => 0.05,
                'normalization_divisor' => 100.0,
                'prng_scale' => 1.0,
            ], JSON_THROW_ON_ERROR),
            'updated_at' => now(),
            'created_at' => now(),
        ],
    );
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('locks a program and persists snapshot with stable scores', function (): void {
    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = app(CreateLotteryProgramAction::class)->execute(
        title: 'Lock Snapshot Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 20,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = app(OpenRegistrationAction::class)->execute($draft->requireId());
    $registration = app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    );
    $closed = app(CloseRegistrationAction::class)->execute($opened->requireId());

    $locked = app(LockLotteryProgramAction::class)->execute($closed->requireId());

    expect($locked->status)->toBe(LockedState::$name);
    expect($locked->randomSeed)->not->toBeNull();
    expect($locked->scoringConfigVersion)->toBe('1.0.0');
    expect($locked->lockedAt)->not->toBeNull();

    $snapshot = app(LotteryEligibleSnapshotRepositoryContract::class)->findByProgramId($locked->requireId());
    if (! $snapshot instanceof EligibleSnapshot) {
        throw new UnexpectedValueException('Expected eligible snapshot.');
    }

    expect($snapshot->randomSeed)->toBe($locked->randomSeed);
    expect($snapshot->payload['eligible'] ?? [])->toHaveCount(1);

    $reloadedRegistration = app(LotteryRegistrationRepositoryContract::class)->findById($registration->requireId());
    expect($reloadedRegistration?->weightedScore)->not->toBeNull();

    $config = new ScoringConfig(
        version: '1.0.0',
        baseScoreCoefficient: 1.0,
        departmentPriorityCoefficient: 0.05,
        normalizationDivisor: 100.0,
        prngScale: 1.0,
    );
    $engine = app(LotteryScoringEngine::class);
    $expectedScore = $engine->computeWeightedScore(
        config: $config,
        randomSeed: (string) $locked->randomSeed,
        registrationId: $registration->requireId()->value,
        baseScore: 0.0,
        departmentPriority: 0,
    );

    expectPersistedWeightedScore($reloadedRegistration?->weightedScore, $expectedScore);
});
