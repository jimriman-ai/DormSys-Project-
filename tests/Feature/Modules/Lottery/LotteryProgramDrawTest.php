<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Application\Contracts\LotteryResultRepositoryContract;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Application\Services\LotteryScoringConfigReader;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Lottery\Domain\Enums\LotteryResultOutcome;
use App\Modules\Lottery\Domain\States\CompletedState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

require_once __DIR__.'/LotteryRegistrationEnrollmentTest.php';

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

it('runs full lifecycle through draw with queryable results', function (): void {
    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = app(CreateLotteryProgramAction::class)->execute(
        title: 'Draw Lifecycle Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 1,
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

    $completed = app(ExecuteDrawAction::class)->execute($locked->requireId());
    $retry = app(ExecuteDrawAction::class)->execute($locked->requireId());

    expect($completed->status)->toBe(CompletedState::$name);
    expect($completed->drawnAt)->not->toBeNull();
    expect($retry->status)->toBe(CompletedState::$name);

    $repositoryResults = app(LotteryResultRepositoryContract::class)->findByProgramId($completed->requireId());
    expect($repositoryResults)->toHaveCount(1);
    expect($repositoryResults[0]->registrationId->value)->toBe($registration->requireId()->value);
    expect($repositoryResults[0]->rank)->toBe(1);
    expect($repositoryResults[0]->outcome)->toBe(LotteryResultOutcome::Winner);

    $readResults = app(LotteryResultReadContract::class)->resultsForProgram($completed->requireId());
    expect($readResults)->toHaveCount(1);
    expect($readResults[0]['registration_id'])->toBe($registration->requireId()->value);
    expect($readResults[0]['program_id'])->toBe($completed->requireId()->value);
    expect($readResults[0]['rank'])->toBe(1);
    expect($readResults[0]['outcome'])->toBe('winner');
});

it('does not duplicate results when draw is retried', function (): void {
    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = app(CreateLotteryProgramAction::class)->execute(
        title: 'Idempotent Draw Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 5,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = app(OpenRegistrationAction::class)->execute($draft->requireId());
    app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    );
    $closed = app(CloseRegistrationAction::class)->execute($opened->requireId());
    $locked = app(LockLotteryProgramAction::class)->execute($closed->requireId());

    app(ExecuteDrawAction::class)->execute($locked->requireId());
    app(ExecuteDrawAction::class)->execute($locked->requireId());

    $results = app(LotteryResultRepositoryContract::class)->findByProgramId($locked->requireId());
    expect($results)->toHaveCount(1);
});
