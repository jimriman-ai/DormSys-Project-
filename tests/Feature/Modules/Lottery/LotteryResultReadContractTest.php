<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Application\Services\LotteryScoringConfigReader;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
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

function createSecondEmployeeForLotteryContractTest(): \App\Modules\Employee\Domain\Entities\Employee
{
    for ($attempt = 0; $attempt < 100; $attempt++) {
        $nine = str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);

        for ($check = 0; $check <= 9; $check++) {
            $candidate = $nine.(string) $check;

            if (\App\Support\ValueObjects\Identity\NationalCode::isValid($candidate)) {
                $nationalCode = \App\Support\ValueObjects\Identity\NationalCode::fromString($candidate);

                $user = app(\App\Modules\Identity\Application\Services\CreateUserAction::class)->execute(
                    'Lottery Contract User',
                    'lottery.contract.'.uniqid('', true).'@example.com',
                );

                return app(\App\Modules\Employee\Application\Services\CreateEmployeeAction::class)->execute(
                    identityId: \App\Modules\Employee\Domain\ValueObjects\IdentityUserId::fromString($user->requireId()->value),
                    employeeCode: 'EMP-LC-'.substr(uniqid('', true), -6),
                    firstName: 'Contract',
                    lastName: 'Reader',
                    nationalCode: $nationalCode,
                    hireDate: new DateTimeImmutable('2024-01-01'),
                );
            }
        }
    }

    throw new RuntimeException('Could not generate a valid national code for lottery contract test.');
}

const LOTTERY_RESULT_READ_CONTRACT_KEYS = ['program_id', 'winners', 'reserves', 'ranks'];

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

function assertLotteryResultReadContractShape(array $payload): void
{
    expect(array_keys($payload))->toEqual(LOTTERY_RESULT_READ_CONTRACT_KEYS);
    expect($payload['winners'])->toBeArray();
    expect($payload['reserves'])->toBeArray();
    expect($payload['ranks'])->toBeArray();

    foreach ($payload['winners'] as $row) {
        expect(array_keys($row))->toEqual(['registration_id', 'rank']);
        expect($row['registration_id'])->toBeString();
        expect($row['rank'])->toBeInt();
    }

    foreach ($payload['reserves'] as $row) {
        expect(array_keys($row))->toEqual(['registration_id', 'rank']);
        expect($row['registration_id'])->toBeString();
        expect($row['rank'])->toBeInt();
    }

    foreach ($payload['ranks'] as $row) {
        expect(array_keys($row))->toEqual(['rank', 'registration_id', 'outcome']);
        expect($row['rank'])->toBeInt();
        expect($row['registration_id'])->toBeString();
        expect($row['outcome'])->toBeIn(['winner', 'reserve']);
    }
}

it('returns the public contract output shape for a completed draw', function (): void {
    $employeeOne = createEmployeeForLotteryEnrollmentTest();
    $employeeTwo = createSecondEmployeeForLotteryContractTest();
    $dormitoryId = UuidGenerator::uuid7();
    $requestOne = createApprovedLotteryRegistrationRequest($employeeOne, $dormitoryId);
    $requestTwo = createApprovedLotteryRegistrationRequest($employeeTwo, $dormitoryId);

    $draft = app(CreateLotteryProgramAction::class)->execute(
        title: 'Contract Read Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 1,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = app(OpenRegistrationAction::class)->execute($draft->requireId());
    $registrationOne = app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestOne),
    );
    $registrationTwo = app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestTwo),
    );
    $closed = app(CloseRegistrationAction::class)->execute($opened->requireId());
    $locked = app(LockLotteryProgramAction::class)->execute($closed->requireId());
    $completed = app(ExecuteDrawAction::class)->execute($locked->requireId());

    $payload = app(LotteryResultReadContract::class)->resultsForProgram($completed->requireId());

    assertLotteryResultReadContractShape($payload);
    expect($payload['program_id'])->toBe($completed->requireId()->value);
    expect($payload['winners'])->toHaveCount(1);
    expect($payload['reserves'])->toHaveCount(1);
    expect($payload['ranks'])->toHaveCount(2);

    $rankValues = array_column($payload['ranks'], 'rank');
    expect($rankValues)->toBe([1, 2]);

    $winnerRegistrationIds = array_column($payload['winners'], 'registration_id');
    $reserveRegistrationIds = array_column($payload['reserves'], 'registration_id');
    $allRegistrationIds = array_column($payload['ranks'], 'registration_id');

    expect($winnerRegistrationIds)->toHaveCount(1);
    expect($reserveRegistrationIds)->toHaveCount(1);
    expect($allRegistrationIds)->toContain($registrationOne->requireId()->value);
    expect($allRegistrationIds)->toContain($registrationTwo->requireId()->value);
    expect($winnerRegistrationIds[0])->not->toBe($reserveRegistrationIds[0]);
});

it('returns empty winners and reserves with stable shape when no draw results exist', function (): void {
    $programId = \App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId::fromString(UuidGenerator::uuid7());

    $payload = app(LotteryResultReadContract::class)->resultsForProgram($programId);

    assertLotteryResultReadContractShape($payload);
    expect($payload['program_id'])->toBe($programId->value);
    expect($payload['winners'])->toBe([]);
    expect($payload['reserves'])->toBe([]);
    expect($payload['ranks'])->toBe([]);
});
