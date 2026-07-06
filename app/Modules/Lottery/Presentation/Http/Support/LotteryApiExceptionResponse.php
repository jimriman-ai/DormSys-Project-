<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Presentation\Http\Support;

use App\Modules\Lottery\Domain\Exceptions\DrawNotAllowedException;
use App\Modules\Lottery\Domain\Exceptions\DuplicateEnrollmentException;
use App\Modules\Lottery\Domain\Exceptions\EligibleSnapshotNotFoundException;
use App\Modules\Lottery\Domain\Exceptions\InvalidLotteryTransitionException;
use App\Modules\Lottery\Domain\Exceptions\LotteryDomainException;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\Exceptions\LotteryValidationException;
use App\Modules\Lottery\Domain\Exceptions\RegistrationClosedException;
use App\Modules\Lottery\Domain\Exceptions\ScoringConfigNotFoundException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class LotteryApiExceptionResponse
{
    public static function fromDomainException(LotteryDomainException $exception): JsonResponse
    {
        return match (true) {
            $exception instanceof LotteryProgramNotFoundException => self::failure(
                $exception->getMessage(),
                Response::HTTP_NOT_FOUND,
            ),
            $exception instanceof InvalidLotteryTransitionException,
            $exception instanceof DrawNotAllowedException => self::failure(
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            ),
            $exception instanceof LotteryValidationException,
            $exception instanceof RegistrationClosedException,
            $exception instanceof DuplicateEnrollmentException,
            $exception instanceof EligibleSnapshotNotFoundException,
            $exception instanceof ScoringConfigNotFoundException => self::failure(
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
