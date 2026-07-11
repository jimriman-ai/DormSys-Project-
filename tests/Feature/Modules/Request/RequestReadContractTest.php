<?php

declare(strict_types=1);

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\DTOs\RequestSummaryDTO;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createEmployeeForReadContractTest(): Employee
{
    $user = createIdentityUserThroughMutation(
        'Read Contract User',
        'read.contract.'.uniqid('', true).'@example.com',
    );

    return createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-RC-'.substr(uniqid('', true), -6),
        firstName: 'Read',
        lastName: 'Contract',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

/**
 * @return array{0: Employee, 1: Request}
 */
function createApprovedPersonalRequestForReadTest(): array
{
    $employee = createEmployeeForReadContractTest();

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $submitted = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));
    $request = $submitted;

    foreach (range(1, 4) as $_) {
        $request = approveRequestStageForTest($request);
    }

    expect($request->status)->toBe(ApprovedState::$name);

    return [$employee, $request];
}

it('returns an approved request summary projection', function (): void {
    [$employee, $request] = createApprovedPersonalRequestForReadTest();
    $reader = app(RequestReadContract::class);

    $summary = $reader->getRequestSummary($request->requireId());
    if (! $summary instanceof RequestSummaryDTO) {
        throw new UnexpectedValueException('Expected request summary.');
    }

    expect($summary->id)->toBe($request->requireId()->value);
    expect($summary->employeeId)->toBe($employee->requireId()->value);
    expect($summary->type)->toBe(RequestType::Personal->value);
    expect($summary->status)->toBe(ApprovedState::$name);
    expect($summary->submittedAt)->not->toBeNull();
});

it('returns null summary for an unknown request id', function (): void {
    $summary = app(RequestReadContract::class)->getRequestSummary(
        RequestId::fromString(UuidGenerator::uuid7()),
    );

    expect($summary)->toBeNull();
});

it('returns empty approval history for an unknown request id', function (): void {
    $history = app(RequestReadContract::class)->getApprovalHistory(
        RequestId::fromString(UuidGenerator::uuid7()),
    );

    expect($history)->toBe([]);
});

it('lists approved requests by employee and type', function (): void {
    [$employee, $request] = createApprovedPersonalRequestForReadTest();
    $reader = app(RequestReadContract::class);

    $byEmployee = $reader->listApprovedByEmployee($employee->requireId()->value);
    $byType = $reader->listApprovedByType(RequestType::Personal->value);

    expect($byEmployee)->toHaveCount(1);
    expect($byEmployee[0]->id)->toBe($request->requireId()->value);
    expect($byType)->toHaveCount(1);
    expect($byType[0]->id)->toBe($request->requireId()->value);
});

it('returns append-only approval history for an approved request', function (): void {
    [, $request] = createApprovedPersonalRequestForReadTest();
    $history = app(RequestReadContract::class)->getApprovalHistory($request->requireId());

    expect($history)->toHaveCount(4);
    expect($history[0]->stage)->toBe('department_manager');
    expect($history[0]->decision)->toBe('approved');
});

it('reports request existence without exposing mutations', function (): void {
    [, $request] = createApprovedPersonalRequestForReadTest();
    $reader = app(RequestReadContract::class);

    expect($reader->requestExists($request->requireId()))->toBeTrue();
    expect($reader->requestExists(RequestId::fromString(UuidGenerator::uuid7())))->toBeFalse();
});
