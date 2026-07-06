<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Presentation\Http\Support;

use App\Modules\CheckIn\Domain\Exceptions\AllocationNotActiveException;
use App\Modules\CheckIn\Domain\Exceptions\NoOpenCheckInRecordException;
use App\Modules\CheckIn\Domain\Exceptions\OpenCheckInRecordExistsException;
use App\Modules\CheckIn\Domain\Exceptions\OperatorRoleRequiredException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CheckInApiExceptionResponse
{
    public static function fromDomainException(\Throwable $exception): JsonResponse
    {
        return match (true) {
            $exception instanceof AllocationNotActiveException,
            $exception instanceof NoOpenCheckInRecordException => self::failure(
                $exception->getMessage(),
                Response::HTTP_NOT_FOUND,
            ),
            $exception instanceof OpenCheckInRecordExistsException => self::failure(
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            ),
            $exception instanceof OperatorRoleRequiredException => self::failure(
                $exception->getMessage(),
                Response::HTTP_FORBIDDEN,
            ),
            default => self::failure(
                $exception->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
        };
    }

    private static function failure(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
