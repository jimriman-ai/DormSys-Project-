<?php

declare(strict_types=1);

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Application\Services\CreateLotteryRegistrationRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
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

function createEmployeeForLotteryRegistrationTest(): Employee
{
    $user = createIdentityUserThroughMutation(
        'Lottery Registration User',
        'lottery.reg.'.uniqid('', true).'@example.com',
    );

    return createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-LR-'.substr(uniqid('', true), -6),
        firstName: 'Lottery',
        lastName: 'Registrant',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

it('persists lottery registration request type through submit and approval', function (): void {
    $employee = createEmployeeForLotteryRegistrationTest();
    $dormitoryId = createDormitorySiteForRequestTests();

    $draft = app(CreateLotteryRegistrationRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    expect($draft->type)->toBe(RequestType::LotteryRegistration);

    $submitted = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));
    expect($submitted->status)->toBe(PendingDepartmentManagerState::$name);

    $request = $submitted;
    foreach (range(1, 4) as $_) {
        $request = approveRequestStageForTest($request);
    }

    expect($request->status)->toBe(ApprovedState::$name);

    $reloaded = app(RequestRepositoryContract::class)->findById($request->requireId());
    expect($reloaded?->type)->toBe(RequestType::LotteryRegistration);

    $approved = app(RequestReadContract::class)->listApprovedByType('lottery_registration');
    expect(collect($approved)->pluck('id'))->toContain($request->requireId()->value);
});

it('does not reference lottery module types in the lottery registration flow (CD-011)', function (): void {
    $reflection = new ReflectionClass(CreateLotteryRegistrationRequestAction::class);
    $source = file_get_contents($reflection->getFileName() ?: '') ?: '';

    expect($source)->not->toContain('App\\Modules\\Lottery\\');
});
