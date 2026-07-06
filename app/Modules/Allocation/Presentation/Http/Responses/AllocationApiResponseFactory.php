<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Presentation\Http\Responses;

use App\Modules\Allocation\Domain\Models\Allocation;
use Illuminate\Http\JsonResponse;

final class AllocationApiResponseFactory
{
    public static function success(Allocation $allocation, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => self::serialize($allocation),
        ], $status);
    }

    /**
     * @return array<string, mixed>
     */
    public static function serialize(Allocation $allocation): array
    {
        return [
            'allocationId' => $allocation->requireId()->value,
            'personId' => $allocation->personId->value,
            'bedId' => $allocation->bedId,
            'status' => $allocation->status->value,
            'method' => $allocation->method->value,
            'dateRangeStart' => $allocation->dateRange->start->format('Y-m-d'),
            'dateRangeEnd' => $allocation->dateRange->end->format('Y-m-d'),
            'sourceRequestId' => $allocation->sourceRequestId,
            'sourceLotteryResultId' => $allocation->sourceLotteryResultId,
            'releasedAt' => $allocation->releasedAt?->format(DATE_ATOM),
            'releaseReason' => $allocation->releaseReason,
        ];
    }
}
