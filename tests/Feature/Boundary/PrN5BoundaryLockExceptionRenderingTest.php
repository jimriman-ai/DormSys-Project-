<?php

declare(strict_types=1);

use App\Modules\Dormitory\Application\Exceptions\PhysicalStateSignalRejectedException;
use App\Modules\Dormitory\Application\Exceptions\UnauthorizedDormitoryStructureAccessException;
use App\Modules\Dormitory\Domain\Exceptions\InvalidCapacity;
use App\Modules\Dormitory\Domain\Exceptions\InvalidDormitoryHierarchy;
use App\Modules\Dormitory\Domain\Exceptions\InvalidOccupancyTransition;
use App\Modules\Dormitory\Domain\Exceptions\InvalidResourceStateTransition;
use App\Modules\Employee\Domain\Exceptions\DepartmentNotFoundException;
use App\Modules\Employee\Domain\Exceptions\DependentNotFoundException;
use App\Modules\Employee\Domain\Exceptions\DependentOwnershipException;
use App\Modules\Employee\Domain\Exceptions\DuplicateDepartmentCodeException;
use App\Modules\Employee\Domain\Exceptions\DuplicateIdentityIdException;
use App\Modules\Employee\Domain\Exceptions\EmployeeNotFoundException;
use App\Modules\Employee\Domain\Exceptions\IdentityIdImmutableException;
use App\Modules\Employee\Domain\Exceptions\InactiveDepartmentAssignmentException;
use App\Modules\Employee\Domain\Exceptions\UnknownIdentityUserException;
use App\Modules\Identity\Domain\Exceptions\CannotDeactivateLastAdministratorException;
use App\Modules\Identity\Domain\Exceptions\DuplicateUserEmailException;
use App\Modules\Identity\Domain\Exceptions\InvalidUserStateTransitionException;
use App\Modules\Identity\Domain\Exceptions\RoleNotFoundException;
use App\Modules\Identity\Domain\Exceptions\UserNotFoundException;
use App\Modules\Voucher\Domain\Exceptions\DuplicateTriggerCorrelationException;
use App\Modules\Voucher\Domain\Exceptions\InvalidVoucherTransitionException;
use App\Modules\Voucher\Domain\Exceptions\VoucherNotEligibleForIssuanceException;
use App\Modules\Voucher\Domain\Exceptions\VoucherReissuanceRejectedException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @return array<string, array{0: \Throwable, 1: int}>
 */
function prN5BoundaryLockExceptionCases(): array
{
    return [
        'employee.not_found' => [new EmployeeNotFoundException('employee missing'), Response::HTTP_NOT_FOUND],
        'employee.department_not_found' => [new DepartmentNotFoundException('department missing'), Response::HTTP_NOT_FOUND],
        'employee.dependent_not_found' => [new DependentNotFoundException('dependent missing'), Response::HTTP_NOT_FOUND],
        'employee.unknown_identity' => [new UnknownIdentityUserException('unknown identity'), Response::HTTP_NOT_FOUND],
        'employee.dependent_ownership' => [new DependentOwnershipException('ownership'), Response::HTTP_FORBIDDEN],
        'employee.duplicate_identity' => [new DuplicateIdentityIdException('duplicate identity'), Response::HTTP_CONFLICT],
        'employee.duplicate_department' => [new DuplicateDepartmentCodeException('duplicate department'), Response::HTTP_CONFLICT],
        'employee.identity_immutable' => [new IdentityIdImmutableException('immutable'), Response::HTTP_UNPROCESSABLE_ENTITY],
        'employee.inactive_department' => [new InactiveDepartmentAssignmentException('inactive dept'), Response::HTTP_UNPROCESSABLE_ENTITY],
        'identity.user_not_found' => [new UserNotFoundException('user missing'), Response::HTTP_NOT_FOUND],
        'identity.role_not_found' => [new RoleNotFoundException('role missing'), Response::HTTP_NOT_FOUND],
        'identity.duplicate_email' => [new DuplicateUserEmailException('duplicate email'), Response::HTTP_CONFLICT],
        'identity.invalid_transition' => [new InvalidUserStateTransitionException('bad transition'), Response::HTTP_CONFLICT],
        'identity.last_admin' => [new CannotDeactivateLastAdministratorException('last admin'), Response::HTTP_UNPROCESSABLE_ENTITY],
        'dormitory.unauthorized' => [new UnauthorizedDormitoryStructureAccessException('forbidden structure'), Response::HTTP_FORBIDDEN],
        'dormitory.invalid_capacity' => [new InvalidCapacity('bad capacity'), Response::HTTP_UNPROCESSABLE_ENTITY],
        'dormitory.invalid_hierarchy' => [new InvalidDormitoryHierarchy('bad hierarchy'), Response::HTTP_UNPROCESSABLE_ENTITY],
        'dormitory.occupancy_transition' => [new InvalidOccupancyTransition('occupancy'), Response::HTTP_CONFLICT],
        'dormitory.resource_transition' => [new InvalidResourceStateTransition('resource'), Response::HTTP_CONFLICT],
        'dormitory.physical_signal' => [new PhysicalStateSignalRejectedException('REJECT', 'signal rejected'), Response::HTTP_UNPROCESSABLE_ENTITY],
        'voucher.not_eligible' => [new VoucherNotEligibleForIssuanceException('not eligible'), Response::HTTP_UNPROCESSABLE_ENTITY],
        'voucher.invalid_transition' => [new InvalidVoucherTransitionException('voucher transition'), Response::HTTP_CONFLICT],
        'voucher.duplicate_trigger' => [new DuplicateTriggerCorrelationException('duplicate trigger'), Response::HTTP_CONFLICT],
        'voucher.reissuance' => [new VoucherReissuanceRejectedException('reissuance rejected'), Response::HTTP_UNPROCESSABLE_ENTITY],
    ];
}

it('maps PR-N5 domain exceptions to approved API HTTP statuses', function (Throwable $exception, int $status): void {
    config(['app.debug' => false]);

    $request = Request::create('/api/pr-n5-boundary-lock', 'GET', server: [
        'HTTP_ACCEPT' => 'application/json',
    ]);

    $response = app(ExceptionHandler::class)->render($request, $exception);

    expect($response->getStatusCode())->toBe($status)
        ->and($response->headers->get('content-type'))->toContain('application/json')
        ->and($response->getData(true))->toMatchArray([
            'success' => false,
            'message' => $exception->getMessage(),
        ]);
})->with(prN5BoundaryLockExceptionCases());

it('does not apply PR-N5 domain exception JSON mapping outside api prefix', function (): void {
    config(['app.debug' => false]);

    $request = Request::create('/web-pr-n5-boundary-lock', 'GET');
    $response = app(ExceptionHandler::class)->render(
        $request,
        new EmployeeNotFoundException('employee missing'),
    );

    expect($response->getStatusCode())->toBe(Response::HTTP_INTERNAL_SERVER_ERROR);
});
