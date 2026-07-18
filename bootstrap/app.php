<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Http\Middleware\EnsureIdentityRole;
use App\Modules\Allocation\Domain\Exceptions\AllocationNotFoundException;
use App\Modules\Allocation\Domain\Exceptions\AllocationOverlapException;
use App\Modules\Allocation\Domain\Exceptions\BedNotAssignableException;
use App\Modules\Allocation\Domain\Exceptions\InvalidAllocationTransitionException;
use App\Modules\Allocation\Presentation\Http\Support\AllocationApiExceptionResponse;
use App\Modules\Audit\Domain\Exceptions\UnauthorizedAuditAccessException;
use App\Modules\Audit\Presentation\Http\Middleware\ResolveAuditPrincipalMiddleware;
use App\Modules\CheckIn\Domain\Exceptions\AllocationNotActiveException as CheckInAllocationNotActiveException;
use App\Modules\CheckIn\Domain\Exceptions\NoOpenCheckInRecordException;
use App\Modules\CheckIn\Domain\Exceptions\OpenCheckInRecordExistsException;
use App\Modules\CheckIn\Domain\Exceptions\OperatorRoleRequiredException;
use App\Modules\CheckIn\Presentation\Http\Support\CheckInApiExceptionResponse;
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
use App\Modules\Identity\Domain\Exceptions\CannotRemoveOwnSystemAdministratorRoleException;
use App\Modules\Identity\Domain\Exceptions\DuplicateUserEmailException;
use App\Modules\Identity\Domain\Exceptions\InvalidUserStateTransitionException;
use App\Modules\Identity\Domain\Exceptions\LastSystemAdministratorException;
use App\Modules\Identity\Domain\Exceptions\ProtectedRoleException;
use App\Modules\Identity\Domain\Exceptions\RoleHasAssignedUsersException;
use App\Modules\Identity\Domain\Exceptions\RoleNotFoundException;
use App\Modules\Identity\Domain\Exceptions\UserNotFoundException;
use App\Modules\Identity\Presentation\Http\Support\IdentityRoleApiExceptionResponse;
use App\Modules\Lottery\Domain\Exceptions\LotteryDomainException;
use App\Modules\Lottery\Presentation\Http\Support\LotteryApiExceptionResponse;
use App\Modules\Reporting\Domain\Exceptions\UnauthorizedArchiveVisibilityException;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\Exceptions\RequestNotEligibleException;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;
use App\Modules\Request\Presentation\Http\Middleware\EnforceSessionMutationPrincipalMiddleware;
use App\Modules\Request\Presentation\Http\Support\RequestApiExceptionResponse;
use App\Modules\Voucher\Domain\Exceptions\DuplicateTriggerCorrelationException;
use App\Modules\Voucher\Domain\Exceptions\InvalidVoucherTransitionException;
use App\Modules\Voucher\Domain\Exceptions\VoucherNotEligibleForIssuanceException;
use App\Modules\Voucher\Domain\Exceptions\VoucherReissuanceRejectedException;
use App\Support\Exceptions\ValidationException as DomainValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Validation\ValidationException as HttpValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException as SpatieUnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
        ]);

        $middleware->alias([
            'audit.principal' => ResolveAuditPrincipalMiddleware::class,
            'request.mutation.principal' => EnforceSessionMutationPrincipalMiddleware::class,
            'identity.role' => EnsureIdentityRole::class,
            'permission' => PermissionMiddleware::class,
        ]);

        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (UnauthorizedAuditAccessException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (UnauthorizedMutationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (SpatieUnauthorizedException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (
            ProtectedRoleException
            |RoleHasAssignedUsersException
            |CannotRemoveOwnSystemAdministratorRoleException
            |LastSystemAdministratorException
            |RoleNotFoundException
            |UserNotFoundException $exception,
            Request $request,
        ) {
            if (! $request->is('api/identity', 'api/identity/*')) {
                return null;
            }

            return IdentityRoleApiExceptionResponse::fromDomainException($exception);
        });

        $exceptions->render(function (UnauthorizedArchiveVisibilityException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (DomainValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $exceptions->render(function (HttpValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $exception->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $exceptions->render(function (RequestNotFoundException $exception, Request $request) {
            if (! $request->is('api/requests', 'api/requests/*')) {
                return null;
            }

            return RequestApiExceptionResponse::fromDomainException($exception);
        });

        $exceptions->render(function (RequestValidationException $exception, Request $request) {
            if (! $request->is('api/requests', 'api/requests/*')) {
                return null;
            }

            return RequestApiExceptionResponse::fromDomainException($exception);
        });

        $exceptions->render(function (RequestNotEligibleException $exception, Request $request) {
            if (! $request->is('api/requests', 'api/requests/*')) {
                return null;
            }

            return RequestApiExceptionResponse::fromDomainException($exception);
        });

        $exceptions->render(function (InvalidRequestTransitionException $exception, Request $request) {
            if (! $request->is('api/requests', 'api/requests/*')) {
                return null;
            }

            return RequestApiExceptionResponse::fromDomainException($exception);
        });

        $exceptions->render(function (LotteryDomainException $exception, Request $request) {
            if (! $request->is('api/lottery', 'api/lottery/*')) {
                return null;
            }

            return LotteryApiExceptionResponse::fromDomainException($exception);
        });

        $exceptions->render(function (AllocationNotFoundException $exception, Request $request) {
            if (! $request->is('api/allocations', 'api/allocations/*')) {
                return null;
            }

            return AllocationApiExceptionResponse::fromAllocationException($exception);
        });

        $exceptions->render(function (InvalidAllocationTransitionException|AllocationOverlapException $exception, Request $request) {
            if ($request->is('api/allocations', 'api/allocations/*')) {
                return AllocationApiExceptionResponse::fromAllocationException($exception);
            }

            if ($request->is('api/lottery', 'api/lottery/*')) {
                return AllocationApiExceptionResponse::fromAllocationException($exception);
            }

            return null;
        });

        $exceptions->render(function (BedNotAssignableException $exception, Request $request) {
            if ($request->is('api/allocations', 'api/allocations/*')) {
                return AllocationApiExceptionResponse::fromAllocationException($exception);
            }

            if ($request->is('api/lottery', 'api/lottery/*')) {
                return AllocationApiExceptionResponse::fromAllocationException($exception);
            }

            return null;
        });

        $exceptions->render(function (
            CheckInAllocationNotActiveException
            |NoOpenCheckInRecordException
            |OpenCheckInRecordExistsException
            |OperatorRoleRequiredException $exception,
            Request $request,
        ) {
            if (! $request->is('api/check-in', 'api/check-in/*')) {
                return null;
            }

            return CheckInApiExceptionResponse::fromDomainException($exception);
        });

        // PR-N5: Employee / Identity / Dormitory / Voucher domain→HTTP (api/* only).
        // After api/identity specialized handler so RoleNotFound/UserNotFound keep IdentityRoleApiExceptionResponse.
        $exceptions->render(function (
            EmployeeNotFoundException
            |DepartmentNotFoundException
            |DependentNotFoundException
            |UnknownIdentityUserException
            |UserNotFoundException
            |RoleNotFoundException $exception,
            Request $request,
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (
            DependentOwnershipException
            |UnauthorizedDormitoryStructureAccessException $exception,
            Request $request,
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (
            DuplicateIdentityIdException
            |DuplicateDepartmentCodeException
            |DuplicateUserEmailException
            |InvalidUserStateTransitionException
            |InvalidOccupancyTransition
            |InvalidResourceStateTransition
            |InvalidVoucherTransitionException
            |DuplicateTriggerCorrelationException $exception,
            Request $request,
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_CONFLICT);
        });

        $exceptions->render(function (
            IdentityIdImmutableException
            |InactiveDepartmentAssignmentException
            |CannotDeactivateLastAdministratorException
            |InvalidCapacity
            |InvalidDormitoryHierarchy
            |PhysicalStateSignalRejectedException
            |VoucherNotEligibleForIssuanceException
            |VoucherReissuanceRejectedException $exception,
            Request $request,
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/reporting/*')) {
                return null;
            }

            if ($exception instanceof AuthenticationException
                || $exception instanceof UnauthorizedAuditAccessException
                || $exception instanceof UnauthorizedMutationException
                || $exception instanceof UnauthorizedArchiveVisibilityException
                || $exception instanceof DomainValidationException
                || $exception instanceof HttpValidationException) {
                return null;
            }

            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Reporting request failed.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    })->create();
