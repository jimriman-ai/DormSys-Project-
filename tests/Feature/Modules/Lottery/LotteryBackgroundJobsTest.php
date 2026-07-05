<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Contracts\LotteryEligibleSnapshotRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryResultRepositoryContract;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Services\LotteryScoringConfigReader;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Lottery\Domain\States\CompletedState;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Lottery\Infrastructure\Jobs\AutoLockLotteryJob;
use App\Modules\Lottery\Infrastructure\Jobs\ExecuteLotteryDrawJob;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

require_once __DIR__.'/LotteryRegistrationEnrollmentTest.php';

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-15 12:00:00');

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

it('auto-locks past-deadline programs idempotently', function (): void {
    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = app(CreateLotteryProgramAction::class)->execute(
        title: 'Auto Lock Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 5,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-10 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = app(OpenRegistrationAction::class)->execute($draft->requireId());
    app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    );

    $job = app(AutoLockLotteryJob::class);
    app()->call([$job, 'handle']);
    app()->call([$job, 'handle']);

    $reloaded = app(LotteryProgramRepositoryContract::class)
        ->findById($opened->requireId());

    expect($reloaded?->status)->toBe(LockedState::$name);
    expect($reloaded?->lockedAt)->not->toBeNull();

    $snapshots = app(LotteryEligibleSnapshotRepositoryContract::class)->findByProgramId($opened->requireId());
    expect($snapshots)->not->toBeNull();
    expect($snapshots?->payload['eligible'] ?? [])->toHaveCount(1);
});

it('executes draw job idempotently for locked programs', function (): void {
    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = app(CreateLotteryProgramAction::class)->execute(
        title: 'Draw Job Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 1,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-10 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = app(OpenRegistrationAction::class)->execute($draft->requireId());
    app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    );

    app()->call([app(AutoLockLotteryJob::class), 'handle']);

    $programId = $opened->requireId()->value;
    $drawJob = new ExecuteLotteryDrawJob($programId);
    app()->call([$drawJob, 'handle']);
    app()->call([$drawJob, 'handle']);

    $program = app(LotteryProgramRepositoryContract::class)
        ->findById($opened->requireId());

    expect($program?->status)->toBe(CompletedState::$name);

    $results = app(LotteryResultRepositoryContract::class)->findByProgramId($opened->requireId());
    expect($results)->toHaveCount(1);
});

it('can dispatch background jobs to the queue', function (): void {
    Queue::fake();

    AutoLockLotteryJob::dispatch();
    ExecuteLotteryDrawJob::dispatch();

    Queue::assertPushed(AutoLockLotteryJob::class, 1);
    Queue::assertPushed(ExecuteLotteryDrawJob::class, 1);
});
