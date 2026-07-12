<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Application\Mutation\Support\MutationPrincipalContext;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Domain\ValueObjects\PersonAllocationRef;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
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
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Modules/Lottery/LotteryRegistrationEnrollmentTest.php';
require_once __DIR__.'/../Modules/Lottery/support/mutation-principal.php';

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-15 12:00:00');
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
    putenv('MUTATION_ACTING_PRINCIPAL');
    unset($_ENV['MUTATION_ACTING_PRINCIPAL'], $_SERVER['MUTATION_ACTING_PRINCIPAL']);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('fails closed when runtime mutation is invoked without an established principal', function (): void {
    $actorId = createActiveMutationActorId('Runtime Governance Actor');
    $dormitoryId = DormitorySiteId::fromString(createDormitorySiteForRequestTests());

    $program = mutationActingAs($actorId, fn () => createLotteryProgramForTest(
        title: 'Runtime Deny Program',
        dormitoryId: $dormitoryId->value,
        capacity: 3,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-10 23:59:59', new DateTimeZone('UTC')),
    ));

    expect(fn () => app(OpenRegistrationAction::class)->execute($program->requireId()))
        ->toThrow(UnauthorizedMutationException::class);

    expect(app(LotteryProgramRepositoryContract::class)->findById($program->requireId())?->isDraft())->toBeTrue();
});

it('does not treat injected environment principal as production runtime authority', function (): void {
    putenv('MUTATION_ACTING_PRINCIPAL='.UuidGenerator::uuid7());

    expect(fn () => app(MutationPolicyEnforcementPoint::class)->enforce(
        MutationCapabilityCatalog::ALLOCATION_CREATE,
    ))->toThrow(UnauthorizedMutationException::class);
});

it('executes auto lock job with explicit approved system actor without test wrappers', function (): void {
    seedLotterySettingsForBackgroundJobs();

    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = createDormitorySiteForRequestTests();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = createLotteryProgramForTest(
        title: 'Runtime Auto Lock Program',
        dormitoryId: $dormitoryId,
        capacity: 5,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-10 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = openLotteryProgramForTest($draft->requireId());
    asRequestOwner($employee, fn () => app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    ));

    app()->call([app(AutoLockLotteryJob::class), 'handle']);

    $reloaded = app(LotteryProgramRepositoryContract::class)->findById($opened->requireId());

    expect($reloaded?->status)->toBe(LockedState::$name);
});

it('executes draw job with explicit approved system actor without test wrappers', function (): void {
    seedLotterySettingsForBackgroundJobs();

    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = createDormitorySiteForRequestTests();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = createLotteryProgramForTest(
        title: 'Runtime Draw Program',
        dormitoryId: $dormitoryId,
        capacity: 1,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-10 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = openLotteryProgramForTest($draft->requireId());
    asRequestOwner($employee, fn () => app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    ));

    app()->call([app(AutoLockLotteryJob::class), 'handle']);

    app()->call([new ExecuteLotteryDrawJob($opened->requireId()->value), 'handle']);

    expect(app(LotteryProgramRepositoryContract::class)->findById($opened->requireId())?->status)
        ->toBe(CompletedState::$name);
});

it('denies user-bound lottery enrollment for approved system actor', function (): void {
    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = createDormitorySiteForRequestTests();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);
    $draft = createLotteryProgramForTest(
        title: 'System Enroll Deny Program',
        dormitoryId: $dormitoryId,
        capacity: 5,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = openLotteryProgramForTest($draft->requireId());

    expect(fn () => MutationPrincipalContext::runAsSystem(fn () => app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    )))->toThrow(UnauthorizedMutationException::class, 'Mutation actor must own the enrollment request.');

    expect(app(LotteryRegistrationRepositoryContract::class)->findByProgramAndRequest(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    ))->toBeNull();
});

it('allows system-driven allocation creation only through approved system actor context', function (): void {
    $personId = UuidGenerator::uuid7();

    $allocation = MutationPrincipalContext::runAsSystem(fn () => app(CreateAllocationAction::class)->execute(
        personId: $personId,
        bedId: createAssignableBedForAllocationTests(),
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    ));

    expect($allocation->isActive())->toBeTrue()
        ->and(app(AllocationRepositoryContract::class)->findActiveByPersonId(PersonAllocationRef::fromString($personId)))->not->toBeEmpty();
});

function seedLotterySettingsForBackgroundJobs(): void
{
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
}
