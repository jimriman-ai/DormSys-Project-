<?php

declare(strict_types=1);

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Request\Application\Contracts\MissionDetailsRepositoryContract;
use App\Modules\Request\Application\Contracts\RequestMemberRepositoryContract;
use App\Modules\Request\Application\Services\CreateMissionRequestAction;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\Events\RequestSubmitted;
use App\Modules\Request\Domain\Exceptions\AppendOnlyViolationException;
use App\Modules\Request\Domain\Exceptions\InvalidGroupRequestException;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestMemberModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function uniqueNationalCodeForMissionTest(): NationalCode
{
    for ($attempt = 0; $attempt < 100; $attempt++) {
        $nine = str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);

        for ($check = 0; $check <= 9; $check++) {
            $candidate = $nine.(string) $check;

            if (NationalCode::isValid($candidate)) {
                return NationalCode::fromString($candidate);
            }
        }
    }

    throw new RuntimeException('Could not generate a valid national code for mission test.');
}

function createEmployeeForMissionTest(string $suffix = ''): Employee
{
    $user = createIdentityUserThroughMutation(
        'Mission Test User '.$suffix,
        'mission.test.'.uniqid('', true).'@example.com',
    );

    return createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-MS-'.substr(uniqid('', true), -6),
        firstName: 'Mission',
        lastName: 'Member'.$suffix,
        nationalCode: uniqueNationalCodeForMissionTest(),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

/**
 * @param  list<Employee>  $employees
 * @return list<array{employeeId: string, isLeader: bool}>
 */
function missionMemberPayload(array $employees, int $leaderIndex): array
{
    return array_values(array_map(
        static fn (int $index, Employee $employee): array => [
            'employeeId' => $employee->requireId()->value,
            'isLeader' => $index === $leaderIndex,
        ],
        array_keys($employees),
        $employees,
    ));
}

/**
 * CreateMissionRequestAction → DormitoryReadBridge::siteExists requires
 * dormitory.structure.view principal (D-L6-5-DORMSTRUCT Option A).
 *
 * @param  list<array{employeeId: string, isLeader: bool}>  $members
 */
function executeMissionWithStructureView(
    Employee $coordinator,
    string $dormitoryId,
    array $members,
    string $description,
    ?string $missionDocumentUrl = null,
): mixed {
    return withDormitoryStructureViewActor(
        fn () => app(CreateMissionRequestAction::class)->execute(
            employeeId: EmployeeReferenceId::fromString($coordinator->requireId()->value),
            dormitoryId: DormitorySiteId::fromString($dormitoryId),
            checkInDate: new DateTimeImmutable('2026-07-01'),
            checkOutDate: new DateTimeImmutable('2026-12-31'),
            members: $members,
            description: $description,
            missionDocumentUrl: $missionDocumentUrl,
        ),
    );
}

/**
 * @return list<array{employeeId: string, isLeader: bool}>
 */
function syntheticMissionMembers(int $count, bool $withLeader = true): array
{
    $members = [];

    for ($i = 0; $i < $count; $i++) {
        $members[] = [
            'employeeId' => UuidGenerator::uuid7(),
            'isLeader' => $withLeader && $i === 0,
        ];
    }

    return $members;
}

it('rejects mission with fewer than two members (BT-R07 / BR-04)', function (): void {
    $coordinator = createEmployeeForMissionTest('coord');

    expect(fn () => executeMissionWithStructureView(
        $coordinator,
        createDormitorySiteForRequestTests(),
        syntheticMissionMembers(1),
        'Single member mission',
    ))->toThrow(InvalidGroupRequestException::class);
});

it('rejects mission with more than twenty members (BT-R07 / BR-04)', function (): void {
    $coordinator = createEmployeeForMissionTest('coord');

    expect(fn () => executeMissionWithStructureView(
        $coordinator,
        createDormitorySiteForRequestTests(),
        syntheticMissionMembers(21),
        'Oversized mission',
    ))->toThrow(InvalidGroupRequestException::class);
});

it('rejects mission with no designated leader (BT-R07 / BR-04)', function (): void {
    $coordinator = createEmployeeForMissionTest('coord');

    expect(fn () => executeMissionWithStructureView(
        $coordinator,
        createDormitorySiteForRequestTests(),
        syntheticMissionMembers(3, withLeader: false),
        'Leaderless mission',
    ))->toThrow(InvalidGroupRequestException::class);
});

it('creates and submits a valid mission with persisted members and details', function (): void {
    Event::fake([RequestSubmitted::class]);

    $coordinator = createEmployeeForMissionTest('coord');
    $memberA = createEmployeeForMissionTest('a');
    $memberB = createEmployeeForMissionTest('b');
    $memberC = createEmployeeForMissionTest('c');

    $request = executeMissionWithStructureView(
        $coordinator,
        createDormitorySiteForRequestTests(),
        missionMemberPayload([$memberA, $memberB, $memberC], leaderIndex: 1),
        'Maintenance crew housing',
        'https://example.com/mission.pdf',
    );

    expect($request->type)->toBe(RequestType::Mission);
    expect($request->status)->toBe(PendingDepartmentManagerState::$name);

    $members = app(RequestMemberRepositoryContract::class)->listForRequest($request->requireId());
    expect($members)->toHaveCount(3);
    expect(collect($members)->where('isLeader', true))->toHaveCount(1);
    expect($members[1]->isLeader)->toBeTrue();

    $details = app(MissionDetailsRepositoryContract::class)->findForRequest($request->requireId());
    expect($details?->description)->toBe('Maintenance crew housing');
    expect($details?->missionDocumentUrl)->toBe('https://example.com/mission.pdf');

    Event::assertDispatched(RequestSubmitted::class, function (RequestSubmitted $event) use ($request): bool {
        return $event->aggregateId === $request->requireId()->value;
    });
});

it('persists mission members as immutable records after submit (CD-014)', function (): void {
    $coordinator = createEmployeeForMissionTest('coord');
    $memberA = createEmployeeForMissionTest('a');
    $memberB = createEmployeeForMissionTest('b');

    $request = executeMissionWithStructureView(
        $coordinator,
        createDormitorySiteForRequestTests(),
        missionMemberPayload([$memberA, $memberB], leaderIndex: 0),
        'Immutable member test',
    );

    $persistedLeaderId = app(RequestMemberRepositoryContract::class)
        ->listForRequest($request->requireId())[0]
        ->employeeId;

    $model = RequestMemberModel::query()->firstOrFail();

    expect(fn () => $model->update(['is_leader' => false]))
        ->toThrow(AppendOnlyViolationException::class);

    $reloaded = app(RequestMemberRepositoryContract::class)->listForRequest($request->requireId());
    expect($reloaded[0]->employeeId)->toBe($persistedLeaderId);
    expect($reloaded[0]->isLeader)->toBeTrue();
});
