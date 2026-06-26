<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class HealthController extends Controller
{
    private const CHECK_OK = 'ok';

    private const CHECK_FAILED = 'failed';

    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
        ];

        $healthy = collect($checks)->every(static fn (string $status): bool => $status === self::CHECK_OK);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'timestamp' => now()->utc()->toIso8601ZuluString(),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();

            return self::CHECK_OK;
        } catch (Throwable) {
            return self::CHECK_FAILED;
        }
    }

    private function checkRedis(): string
    {
        try {
            $response = Redis::ping();

            return $response ? self::CHECK_OK : self::CHECK_FAILED;
        } catch (Throwable) {
            return self::CHECK_FAILED;
        }
    }
}
