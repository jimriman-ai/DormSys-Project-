<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Registry\PendingMutationAuthorizationRegistry;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
use App\Modules\Lottery\Application\Services\CancelLotteryProgramAction;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Request\Application\Services\CreateLotteryRegistrationRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId as RequestDormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-30 12:00:00');
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createLotteryMutationActor(): string
{
    return createActiveMutationActorId('Lottery Mutation Actor');
}

function createEmployeeForLotteryMutationAuthTest(string $nationalCode = '0499370899'): Employee
{
    $user = createIdentityUserThroughMutation(
        'Lottery Mutation Employee',
        'lottery.mutation.'.uniqid('', true).'@example.com',
    );

    return createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-LMA-'.substr(uniqid('', true), -6),
        firstName: 'Lottery',
        lastName: 'Mutator',
        nationalCode: NationalCode::fromString($nationalCode),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

function createApprovedLotteryRequestForMutationAuth(Employee $employee, string $dormitoryId): string
{
    $draft = app(CreateLotteryRegistrationRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: RequestDormitorySiteId::fromString($dormitoryId),
        checkInDate: now('UTC')->addDay()->startOfDay()->toDateTimeImmutable(),
        checkOutDate: now('UTC')->addMonths(6)->startOfDay()->toDateTimeImmutable(),
    );

    $request = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));

    foreach (range(1, 4) as $_) {
        $request = approveRequestStageForTest($request);
    }

    expect($request->status)->toBe(ApprovedState::$name);

    return $request->requireId()->value;
}

it('denies lottery program create without a mutation principal', function (): void {
    $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

    expect(fn () => app(CreateLotteryProgramAction::class)->execute(
        title: 'Denied Program',
        dormitoryId: $dormitoryId,
        capacity: 10,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    ))->toThrow(UnauthorizedMutationException::class);

    expect(app(LotteryProgramRepositoryContract::class)->findById(
        \App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId::fromString(UuidGenerator::uuid7()),
    ))->toBeNull();
});

it('allows lottery program create with an authorized actor', function (): void {
    $actorId = createLotteryMutationActor();
    $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

    $program = mutationActingAs($actorId, fn () => app(CreateLotteryProgramAction::class)->execute(
        title: 'Authorized Program',
        dormitoryId: $dormitoryId,
        capacity: 10,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    ));

    expect($program->isDraft())->toBeTrue();
});

it('denies enrollment when principal does not own the request', function (): void {
    $owner = createEmployeeForLotteryMutationAuthTest();
    $other = createEmployeeForLotteryMutationAuthTest('0000000019');
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = createApprovedLotteryRequestForMutationAuth($owner, $dormitoryId);
    $actorId = createLotteryMutationActor();

    $program = mutationActingAs($actorId, fn () => app(CreateLotteryProgramAction::class)->execute(
        title: 'Enrollment Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 5,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    ));

    $opened = mutationActingAs($actorId, fn () => app(OpenRegistrationAction::class)->execute($program->requireId()));

    expect(fn () => mutationActingAs($other->identityId->value, fn () => app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    )))->toThrow(UnauthorizedMutationException::class, 'Mutation actor must own the enrollment request.');

    expect(app(LotteryRegistrationRepositoryContract::class)->findByProgramAndRequest(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    ))->toBeNull();
});

it('allows enrollment when principal owns the request', function (): void {
    $employee = createEmployeeForLotteryMutationAuthTest();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = createApprovedLotteryRequestForMutationAuth($employee, $dormitoryId);
    $actorId = createLotteryMutationActor();

    $program = mutationActingAs($actorId, fn () => app(CreateLotteryProgramAction::class)->execute(
        title: 'Owner Enrollment Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 5,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    ));

    $opened = mutationActingAs($actorId, fn () => app(OpenRegistrationAction::class)->execute($program->requireId()));

    $registration = asRequestOwner($employee, fn () => app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    ));

    expect($registration->employeeId->value)->toBe($employee->requireId()->value);
});

it('denies program lifecycle mutations without a mutation principal', function (): void {
    $actorId = createLotteryMutationActor();
    $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

    $program = mutationActingAs($actorId, fn () => app(CreateLotteryProgramAction::class)->execute(
        title: 'Lifecycle Program',
        dormitoryId: $dormitoryId,
        capacity: 5,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    ));

    expect(fn () => app(OpenRegistrationAction::class)->execute($program->requireId()))
        ->toThrow(UnauthorizedMutationException::class);
    expect(fn () => app(CloseRegistrationAction::class)->execute($program->requireId()))
        ->toThrow(UnauthorizedMutationException::class);
    expect(fn () => app(CancelLotteryProgramAction::class)->execute($program->requireId(), 'test'))
        ->toThrow(UnauthorizedMutationException::class);
    expect(fn () => app(LockLotteryProgramAction::class)->execute($program->requireId()))
        ->toThrow(UnauthorizedMutationException::class);
    expect(fn () => app(ExecuteDrawAction::class)->execute($program->requireId()))
        ->toThrow(UnauthorizedMutationException::class);
});

it('registers lottery actions as enforced rather than pending', function (): void {
    expect(PendingMutationAuthorizationRegistry::isPending(CreateLotteryProgramAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(OpenRegistrationAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(CloseRegistrationAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(CancelLotteryProgramAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(EnrollRegistrationAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(LockLotteryProgramAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(ExecuteDrawAction::class))->toBeFalse();
});

it('registers lottery mutation capability keys', function (): void {
    expect(MutationCapabilityCatalog::registeredKeys())->toContain(
        MutationCapabilityCatalog::LOTTERY_PROGRAM_CREATE,
        MutationCapabilityCatalog::LOTTERY_PROGRAM_OPEN_REGISTRATION,
        MutationCapabilityCatalog::LOTTERY_PROGRAM_CLOSE_REGISTRATION,
        MutationCapabilityCatalog::LOTTERY_PROGRAM_CANCEL,
        MutationCapabilityCatalog::LOTTERY_PROGRAM_LOCK,
        MutationCapabilityCatalog::LOTTERY_PROGRAM_DRAW,
        MutationCapabilityCatalog::LOTTERY_ENROLL_OWN,
    );
});
