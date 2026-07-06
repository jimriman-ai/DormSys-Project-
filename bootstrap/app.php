<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Modules\Audit\Domain\Exceptions\UnauthorizedAuditAccessException;
use App\Modules\Audit\Presentation\Http\Middleware\ResolveAuditPrincipalMiddleware;
use App\Modules\Lottery\Domain\Exceptions\LotteryDomainException;
use App\Modules\Lottery\Presentation\Http\Support\LotteryApiExceptionResponse;
use App\Modules\Allocation\Domain\Exceptions\AllocationNotFoundException;
use App\Modules\Allocation\Domain\Exceptions\AllocationOverlapException;
use App\Modules\Allocation\Domain\Exceptions\BedNotAssignableException;
use App\Modules\Allocation\Domain\Exceptions\InvalidAllocationTransitionException;
use App\Modules\Allocation\Presentation\Http\Support\AllocationApiExceptionResponse;
use App\Modules\CheckIn\Domain\Exceptions\AllocationNotActiveException as CheckInAllocationNotActiveException;
use App\Modules\CheckIn\Domain\Exceptions\NoOpenCheckInRecordException;
use App\Modules\CheckIn\Domain\Exceptions\OpenCheckInRecordExistsException;
use App\Modules\CheckIn\Domain\Exceptions\OperatorRoleRequiredException;
use App\Modules\CheckIn\Presentation\Http\Support\CheckInApiExceptionResponse;
use App\Modules\Reporting\Domain\Exceptions\UnauthorizedArchiveVisibilityException;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\Exceptions\RequestNotEligibleException;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;
use App\Modules\Request\Presentation\Http\Middleware\EnforceSessionMutationPrincipalMiddleware;
use App\Modules\Request\Presentation\Http\Support\RequestApiExceptionResponse;
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
        ]);
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
            if (! $request->is('api/requests/*')) {
                return null;
            }

            return RequestApiExceptionResponse::fromDomainException($exception);
        });

        $exceptions->render(function (RequestValidationException $exception, Request $request) {
            if (! $request->is('api/requests/*')) {
                return null;
            }

            return RequestApiExceptionResponse::fromDomainException($exception);
        });

        $exceptions->render(function (RequestNotEligibleException $exception, Request $request) {
            if (! $request->is('api/requests/*')) {
                return null;
            }

            return RequestApiExceptionResponse::fromDomainException($exception);
        });

        $exceptions->render(function (InvalidRequestTransitionException $exception, Request $request) {
            if (! $request->is('api/requests/*')) {
                return null;
            }

            return RequestApiExceptionResponse::fromDomainException($exception);
        });

        $exceptions->render(function (LotteryDomainException $exception, Request $request) {
            if (! $request->is('api/lottery/*')) {
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
            if (! $request->is('api/allocations', 'api/allocations/*')) {
                return null;
            }

            return AllocationApiExceptionResponse::fromAllocationException($exception);
        });

        $exceptions->render(function (BedNotAssignableException $exception, Request $request) {
            if (! $request->is('api/allocations', 'api/allocations/*')) {
                return null;
            }

            return AllocationApiExceptionResponse::fromAllocationException($exception);
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
