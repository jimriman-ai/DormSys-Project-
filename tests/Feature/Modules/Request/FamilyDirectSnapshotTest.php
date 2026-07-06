<?php

declare(strict_types=1);

use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Request\Application\Contracts\DependentSnapshotRepositoryContract;
use App\Modules\Request\Application\DTOs\DependentSnapshotReadDTO;
use App\Modules\Request\Application\Services\CreateFamilyDirectRequestAction;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\Events\RequestSubmitted;
use App\Modules\Request\Domain\Exceptions\AppendOnlyViolationException;
use App\Modules\Request\Domain\Exceptions\RequestNotEligibleException;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Infrastructure\Adapters\DependentSnapshotSourceStub;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestDependentSnapshotModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
    app(DependentSnapshotSourceStub::class)->clear();
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createEmployeeForFamilyDirectTest(): Employee
{
    $user = createIdentityUserThroughMutation(
        'Family Direct User',
        'family.direct.'.uniqid('', true).'@example.com',
    );

    return createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-FD-'.substr(uniqid('', true), -6),
        firstName: 'Family',
        lastName: 'Direct',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

function seedEligibleDependentSnapshot(Employee $employee, ?string $dependentId = null): string
{
    $dependentId ??= UuidGenerator::uuid7();
    $stub = app(DependentSnapshotSourceStub::class);

    $stub->seed(new DependentSnapshotReadDTO(
        sourceDependentId: $dependentId,
        ownerEmployeeId: $employee->requireId()->value,
        firstName: 'Sara',
        lastName: 'Snapshot',
        relationship: 'child',
        nationalCode: '1234567890',
        eligible: true,
    ));

    return $dependentId;
}

it('creates and submits a family direct request with immutable dependent snapshots (BT-R06)', function (): void {
    Event::fake([RequestSubmitted::class]);

    $employee = createEmployeeForFamilyDirectTest();
    $dependentId = seedEligibleDependentSnapshot($employee);

    $request = app(CreateFamilyDirectRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
        sourceDependentIds: [$dependentId],
    );

    expect($request->type)->toBe(RequestType::FamilyDirect);
    expect($request->status)->toBe(PendingDepartmentManagerState::$name);

    $snapshots = app(DependentSnapshotRepositoryContract::class)->listForRequest($request->requireId());
    expect($snapshots)->toHaveCount(1);
    expect($snapshots[0]->firstName)->toBe('Sara');
    expect($snapshots[0]->lastName)->toBe('Snapshot');
    expect($snapshots[0]->sourceDependentId)->toBe($dependentId);

    app(DependentSnapshotSourceStub::class)->mutateSnapshot(
        employeeId: $employee->requireId()->value,
        sourceDependentId: $dependentId,
        firstName: 'Changed',
        lastName: 'Name',
    );

    $reloaded = app(DependentSnapshotRepositoryContract::class)->listForRequest($request->requireId());
    expect($reloaded[0]->firstName)->toBe('Sara');
    expect($reloaded[0]->lastName)->toBe('Snapshot');

    Event::assertDispatched(RequestSubmitted::class, function (RequestSubmitted $event) use ($request): bool {
        return $event->aggregateId === $request->requireId()->value;
    });
});

it('rejects submission when employee eligibility fails (CD-013)', function (): void {
    $employee = createEmployeeForFamilyDirectTest();
    $dependentId = seedEligibleDependentSnapshot($employee);
    $employee->deactivate();
    app(EmployeeRepositoryContract::class)->save($employee);

    expect(fn () => app(CreateFamilyDirectRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
        sourceDependentIds: [$dependentId],
    ))->toThrow(RequestNotEligibleException::class, 'Request is not eligible for submission.');
});

it('rejects submission when dependent snapshot source is missing', function (): void {
    $employee = createEmployeeForFamilyDirectTest();

    expect(fn () => app(CreateFamilyDirectRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
        sourceDependentIds: [UuidGenerator::uuid7()],
    ))->toThrow(RequestValidationException::class);
});

it('rejects submission when dependent snapshot source marks dependent ineligible', function (): void {
    $employee = createEmployeeForFamilyDirectTest();
    $dependentId = UuidGenerator::uuid7();

    app(DependentSnapshotSourceStub::class)->seed(new DependentSnapshotReadDTO(
        sourceDependentId: $dependentId,
        ownerEmployeeId: $employee->requireId()->value,
        firstName: 'Ineligible',
        lastName: 'Dependent',
        relationship: 'child',
        nationalCode: null,
        eligible: false,
    ));

    expect(fn () => app(CreateFamilyDirectRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
        sourceDependentIds: [$dependentId],
    ))->toThrow(RequestValidationException::class);
});

it('blocks updates to dependent snapshot records (CD-009 append-only)', function (): void {
    $employee = createEmployeeForFamilyDirectTest();
    $dependentId = seedEligibleDependentSnapshot($employee);

    app(CreateFamilyDirectRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
        sourceDependentIds: [$dependentId],
    );

    $model = RequestDependentSnapshotModel::query()->firstOrFail();

    expect(fn () => $model->update(['first_name' => 'tampered']))
        ->toThrow(AppendOnlyViolationException::class);
});

it('does not reference spec03 domain types in the family direct flow', function (): void {
    $reflection = new ReflectionClass(CreateFamilyDirectRequestAction::class);
    $uses = array_keys($reflection->getTraits());

    expect($uses)->toBe([]);

    foreach ($reflection->getFileName() ? token_get_all(file_get_contents($reflection->getFileName()) ?: '') : [] as $token) {
        if (is_array($token) && $token[0] === T_USE) {
            expect($token[1])->not->toContain('App\\Modules\\Employee\\Domain');
        }
    }
});
