<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Presentation\Http\Responses;

use App\Modules\CheckIn\Domain\Models\CheckInRecord;
use Illuminate\Http\JsonResponse;

final class CheckInApiResponseFactory
{
    public static function success(CheckInRecord $record, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => self::serialize($record),
        ], $status);
    }

    /**
     * @return array<string, mixed>
     */
    public static function serialize(CheckInRecord $record): array
    {
        return [
            'checkInRecordId' => $record->requireId()->value,
            'allocationId' => $record->allocationId,
            'operatorId' => $record->operatorId,
            'checkedInAt' => $record->checkedInAt->format(DATE_ATOM),
            'checkedOutAt' => $record->checkedOutAt?->format(DATE_ATOM),
            'isCheckedOut' => $record->isCheckedOut(),
        ];
    }
}
