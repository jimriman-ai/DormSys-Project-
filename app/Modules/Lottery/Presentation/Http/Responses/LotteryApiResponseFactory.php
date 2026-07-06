<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Presentation\Http\Responses;

use App\Modules\Lottery\Application\DTOs\LotteryProgramSummaryDTO;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\Models\LotteryRegistration;
use Illuminate\Http\JsonResponse;

final class LotteryApiResponseFactory
{
    public static function successProgram(LotteryProgram $program, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => self::serializeProgram($program),
        ], $status);
    }

    public static function successRegistration(LotteryRegistration $registration, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => self::serializeRegistration($registration),
        ], $status);
    }

    /**
     * @param  array<string, mixed>  $results
     */
    public static function successResults(array $results): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function serializeProgram(LotteryProgram $program): array
    {
        return [
            'id' => $program->requireId()->value,
            'title' => $program->title,
            'dormitoryId' => $program->dormitoryId->value,
            'capacity' => $program->capacity,
            'registrationStartsAt' => $program->registrationStartsAt->format(DATE_ATOM),
            'registrationEndsAt' => $program->registrationEndsAt->format(DATE_ATOM),
            'status' => $program->status,
            'randomSeed' => $program->randomSeed,
            'scoringConfigVersion' => $program->scoringConfigVersion,
            'cancelledReason' => $program->cancelledReason,
            'lockedAt' => $program->lockedAt?->format(DATE_ATOM),
            'drawnAt' => $program->drawnAt?->format(DATE_ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function serializeProgramSummary(LotteryProgramSummaryDTO $summary): array
    {
        return [
            'id' => $summary->id,
            'title' => $summary->title,
            'dormitoryId' => $summary->dormitoryId,
            'capacity' => $summary->capacity,
            'registrationStartsAt' => $summary->registrationStartsAt,
            'registrationEndsAt' => $summary->registrationEndsAt,
            'status' => $summary->status,
            'randomSeed' => $summary->randomSeed,
            'scoringConfigVersion' => $summary->scoringConfigVersion,
            'cancelledReason' => $summary->cancelledReason,
            'lockedAt' => $summary->lockedAt,
            'drawnAt' => $summary->drawnAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function serializeRegistration(LotteryRegistration $registration): array
    {
        return [
            'id' => $registration->requireId()->value,
            'programId' => $registration->programId->value,
            'requestId' => $registration->requestId->value,
            'employeeId' => $registration->employeeId->value,
            'enrolledAt' => $registration->enrolledAt->format(DATE_ATOM),
            'weightedScore' => $registration->weightedScore,
        ];
    }
}
