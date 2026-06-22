<?php

namespace Tests\Feature\Foundation;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class RedisConnectionTest extends TestCase
{
    public function test_redis_ping_succeeds(): void
    {
        $this->assertTrue((bool) Redis::connection()->ping());
    }

    public function test_cache_round_trip_via_redis(): void
    {
        Cache::put('health-check', 'ok', 60);

        $this->assertSame('ok', Cache::get('health-check'));
    }
}
