<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Http\Support;

use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\Exceptions\RequestDomainException;
use App\Modules\Request\Domain\Exceptions\RequestNotEligibleException;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class RequestApiExceptionResponse
{
    public static function fromDomainException(RequestDomainException $exception): JsonResponse
    {
        return match (true) {
            $exception instanceof RequestNotFoundException => self::failure(
                $exception->getMessage(),
                Response::HTTP_NOT_FOUND,
            ),
            $exception instanceof RequestValidationException,
            $exception instanceof RequestNotEligibleException => self::failure(
                $exception->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
            $exception instanceof InvalidRequestTransitionException => self::failure(
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            ),
            default => self::failure(
                $exception->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
        };
    }

    public static function forbidden(string $message): JsonResponse
    {
        return self::failure($message, Response::HTTP_FORBIDDEN);
    }

    private static function failure(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
