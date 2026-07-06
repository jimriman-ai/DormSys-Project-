<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Presentation\Http\Support;

use App\Modules\Allocation\Domain\Exceptions\AllocationNotFoundException;
use App\Modules\Allocation\Domain\Exceptions\AllocationOverlapException;
use App\Modules\Allocation\Domain\Exceptions\BedNotAssignableException;
use App\Modules\Allocation\Domain\Exceptions\InvalidAllocationTransitionException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class AllocationApiExceptionResponse
{
    public static function fromAllocationException(\Throwable $exception): JsonResponse
    {
        return match (true) {
            $exception instanceof AllocationNotFoundException => self::failure(
                $exception->getMessage(),
                Response::HTTP_NOT_FOUND,
            ),
            $exception instanceof InvalidAllocationTransitionException,
            $exception instanceof AllocationOverlapException => self::failure(
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            ),
            $exception instanceof BedNotAssignableException => self::failure(
                $exception->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
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
