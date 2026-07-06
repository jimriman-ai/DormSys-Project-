<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Application\Mutation\Support\MutationPrincipalContext;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\ProposedAllocationConsumer;
use App\Modules\CheckIn\Application\Contracts\CheckInCommandPort;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Shared\ValueObjects\SystemActorId;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

it('denies missing principal with UnauthorizedMutationException across in-scope modules', function (callable $attempt): void {
    expect($attempt)->toThrow(UnauthorizedMutationException::class);
})->with([
    'identity' => [fn () => app(CreateUserAction::class)->execute('Surface Stabilization', 'surface@example.com')],
    'request' => [fn () => app(MutationPolicyEnforcementPoint::class)->enforce(MutationCapabilityCatalog::REQUEST_APPROVE)],
    'checkin' => [fn () => app(CheckInCommandPort::class)->checkIn(UuidGenerator::uuid7(), UuidGenerator::uuid7())],
    'lottery' => [fn () => app(CreateLotteryProgramAction::class)->execute(
        title: 'Surface Stabilization Program',
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        capacity: 1,
        registrationStartsAt: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    )],
    'allocation' => [fn () => app(CreateAllocationAction::class)->execute(
        personId: UuidGenerator::uuid7(),
        bedId: UuidGenerator::uuid7(),
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    )],
    'consumer' => [fn () => app(ProposedAllocationConsumer::class)->emitProposedAllocations([
        [
            'program_id' => UuidGenerator::uuid7(),
            'registration_id' => UuidGenerator::uuid7(),
            'employee_id' => UuidGenerator::uuid7(),
            'dormitory_id' => UuidGenerator::uuid7(),
            'rank' => 1,
        ],
    ])],
]);

it('does not allow system actor for user-bound lottery enrollment', function (): void {
    require_once __DIR__.'/../Modules/Lottery/LotteryRegistrationEnrollmentTest.php';

    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);
    $draft = createLotteryProgramForTest(
        title: 'Surface Stabilization Enroll Deny',
        dormitoryId: $dormitoryId,
        capacity: 5,
        registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31', new DateTimeZone('UTC')),
    );
    $opened = openLotteryProgramForTest($draft->requireId());

    expect(fn () => MutationPrincipalContext::runAsSystem(
        fn () => app(EnrollRegistrationAction::class)->execute(
            $opened->requireId(),
            RequestReferenceId::fromString($requestId),
        ),
    ))->toThrow(UnauthorizedMutationException::class, 'Mutation actor must own the enrollment request.');
});

it('preserves consistent missing-principal message from MPEP', function (): void {
    expect(fn () => app(CreateUserAction::class)->execute('Message Consistency', null))
        ->toThrow(UnauthorizedMutationException::class, 'Mutation requires an authorized principal.');
});

it('does not treat direct holder assignment as an approved production principal path', function (): void {
    app(MutationPrincipalContextHolder::class)->set(SystemActorId::VALUE);

    expect(app(MutationPrincipalContextHolder::class)->get())->toBe(SystemActorId::VALUE);

    app(MutationPrincipalContextHolder::class)->clear();

    expect(fn () => app(CreateUserAction::class)->execute('Holder Is Not Production Path', null))
        ->toThrow(UnauthorizedMutationException::class);
});
