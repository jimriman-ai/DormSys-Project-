<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Presentation\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ReportingApiResponseFactory
{
    public static function success(string $ru, object $readModel): JsonResponse
    {
        [$data, $provenance] = ReportingReadModelSerializer::split($readModel);

        return response()->json([
            'success' => true,
            'ru' => $ru,
            'timestamp' => now()->utc()->toIso8601ZuluString(),
            'data' => $data,
            'provenance' => $provenance,
        ]);
    }
}
