<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;

/**
 * @return array{employee: Employee, identity: UserModel, email: string, password: string}
 */
function createRequestHttpMutationEmployee(
    string $nationalCode = '0499370899',
    ?string $email = null,
    string $password = 'secret-password',
): array {
    $email ??= 'request.http.'.uniqid('', true).'@example.com';

    User::factory()->create([
        'email' => $email,
        'password' => $password,
    ]);

    $identityUser = app(CreateUserAction::class)->execute('Request HTTP User', $email);
    $employee = app(CreateEmployeeAction::class)->execute(
        identityId: IdentityUserId::fromString($identityUser->requireId()->value),
        employeeCode: 'EMP-HTTP-'.substr(uniqid('', true), -6),
        firstName: 'HTTP',
        lastName: 'Mutation',
        nationalCode: NationalCode::fromString($nationalCode),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );

    $identity = UserModel::query()->findOrFail($identityUser->requireId()->value);

    return [
        'employee' => $employee,
        'identity' => $identity,
        'email' => $email,
        'password' => $password,
    ];
}

function authenticateRequestHttpMutationUser(UserModel $identity): void
{
    test()->actingAs($identity, 'api');
    request()->attributes->set('audit_principal_user_id', $identity->id);
}

function createDraftPersonalRequestForHttp(Employee $employee): Request
{
    return app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );
}

function requestHttpMutationUrl(string $requestId, string $action): string
{
    return "/api/requests/{$requestId}/{$action}";
}
