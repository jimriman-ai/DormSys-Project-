<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Redis;

abstract class TestCase extends BaseTestCase
{
    private static bool $testEnvironmentPrepared = false;

    protected function setUp(): void
    {
        $this->prepareTestEnvironment();

        parent::setUp();

        $this->enforceRedisAndQueueConfiguration();
    }

    private function prepareTestEnvironment(): void
    {
        if (self::$testEnvironmentPrepared) {
            return;
        }

        self::$testEnvironmentPrepared = true;

        $configCache = dirname(__DIR__).'/bootstrap/cache/config.php';

        if (is_file($configCache)) {
            @unlink($configCache);
        }

        if (! $this->redisExtensionIsAvailable()) {
            $this->putEnv('REDIS_CLIENT', 'predis');
        }

        $this->putEnv('QUEUE_CONNECTION', 'redis');
        $this->putEnv('CACHE_STORE', 'redis');
    }

    private function enforceRedisAndQueueConfiguration(): void
    {
        $overrides = [
            'queue.default' => 'redis',
            'cache.default' => 'redis',
        ];

        if (! $this->redisExtensionIsAvailable()) {
            $overrides['database.redis.client'] = 'predis';
        }

        config($overrides);

        if ($this->app->bound('redis')) {
            Redis::purge('default');
            Redis::purge('cache');
        }
    }

    private function redisExtensionIsAvailable(): bool
    {
        return extension_loaded('redis') || class_exists('Redis', false);
    }

    private function putEnv(string $key, string $value): void
    {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    protected function tearDown(): void
    {
        if (function_exists('resetMutationAuthorizationTestState')) {
            resetMutationAuthorizationTestState();
        }

        parent::tearDown();
    }
}
