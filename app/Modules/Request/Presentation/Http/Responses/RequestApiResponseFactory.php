<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Http\Responses;

use App\Modules\Request\Application\DTOs\RequestSummaryDTO;
use App\Modules\Request\Domain\Entities\Request;
use Illuminate\Http\JsonResponse;

final class RequestApiResponseFactory
{
    public static function success(Request $request, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => self::serialize($request),
        ], $status);
    }

    /**
     * @return array<string, mixed>
     */
    public static function serialize(Request $request): array
    {
        return [
            'id' => $request->requireId()->value,
            'code' => $request->code->value,
            'employeeId' => $request->employeeId->value,
            'dormitoryId' => $request->dormitoryId->value,
            'type' => $request->type->value,
            'status' => $request->status,
            'checkInDate' => $request->checkInDate->format('Y-m-d'),
            'checkOutDate' => $request->checkOutDate->format('Y-m-d'),
            'submittedAt' => $request->submittedAt?->format(DATE_ATOM),
            'cancelledAt' => $request->cancelledAt?->format(DATE_ATOM),
            'rejectionReason' => $request->rejectionReason,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function serializeSummary(RequestSummaryDTO $summary): array
    {
        return [
            'id' => $summary->id,
            'code' => $summary->code,
            'employeeId' => $summary->employeeId,
            'dormitoryId' => $summary->dormitoryId,
            'type' => $summary->type,
            'status' => $summary->status,
            'checkInDate' => $summary->checkInDate,
            'checkOutDate' => $summary->checkOutDate,
            'submittedAt' => $summary->submittedAt,
            'cancelledAt' => $summary->cancelledAt,
            'rejectionReason' => $summary->rejectionReason,
        ];
    }
}
