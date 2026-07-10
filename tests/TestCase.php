<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Redis;

abstract class TestCase extends BaseTestCase
{
    private static bool $testEnvironmentPrepared = false;

    private static bool $viteManifestPrepared = false;

    protected function setUp(): void
    {
        $this->prepareTestEnvironment();

        parent::setUp();

        $this->ensureViteManifestForTests();
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

    private function ensureViteManifestForTests(): void
    {
        if (self::$viteManifestPrepared) {
            return;
        }

        self::$viteManifestPrepared = true;

        $manifestPath = public_path('build/manifest.json');

        if (is_file($manifestPath)) {
            return;
        }

        if (! is_dir(dirname($manifestPath))) {
            mkdir(dirname($manifestPath), 0777, true);
        }

        file_put_contents($manifestPath, json_encode([
            'resources/css/app.css' => [
                'file' => 'assets/app.css',
                'src' => 'resources/css/app.css',
                'isEntry' => true,
            ],
            'resources/js/app.js' => [
                'file' => 'assets/app.js',
                'src' => 'resources/js/app.js',
                'isEntry' => true,
            ],
        ], JSON_THROW_ON_ERROR));
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
