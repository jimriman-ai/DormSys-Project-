<?php

declare(strict_types=1);

namespace Tests\Feature\Foundation;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Mockery;
use PDO;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_returns_ok_json_when_dependencies_are_available(): void
    {
        $this->mockHealthyDependencies();

        $response = $this->getJson('/api/health');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => [
                    'database',
                    'redis',
                ],
            ])
            ->assertJson([
                'status' => 'ok',
                'checks' => [
                    'database' => 'ok',
                    'redis' => 'ok',
                ],
            ]);

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/',
            (string) $response->json('timestamp')
        );
    }

    public function test_health_endpoint_returns_degraded_json_when_database_is_unavailable(): void
    {
        $this->mockHealthyRedis();

        DB::shouldReceive('connection')
            ->once()
            ->andThrow(new \RuntimeException('Database unavailable'));

        $response = $this->getJson('/api/health');

        $response
            ->assertStatus(503)
            ->assertJson([
                'status' => 'degraded',
                'checks' => [
                    'database' => 'failed',
                    'redis' => 'ok',
                ],
            ])
            ->assertJsonStructure([
                'timestamp',
            ]);
    }

    public function test_health_endpoint_returns_degraded_json_when_redis_is_unavailable(): void
    {
        $this->mockHealthyDatabase();

        Redis::shouldReceive('ping')
            ->once()
            ->andThrow(new \RuntimeException('Redis unavailable'));

        $response = $this->getJson('/api/health');

        $response
            ->assertStatus(503)
            ->assertJson([
                'status' => 'degraded',
                'checks' => [
                    'database' => 'ok',
                    'redis' => 'failed',
                ],
            ]);
    }

    private function mockHealthyDependencies(): void
    {
        $this->mockHealthyDatabase();
        $this->mockHealthyRedis();
    }

    private function mockHealthyDatabase(): void
    {
        $pdo = Mockery::mock(PDO::class);
        $databaseConnection = Mockery::mock();
        /** @phpstan-ignore method.notFound */
        $databaseConnection->shouldReceive('getPdo')->andReturn($pdo);

        DB::shouldReceive('connection')->andReturn($databaseConnection);
    }

    private function mockHealthyRedis(): void
    {
        Redis::shouldReceive('ping')->andReturn(true);
    }
}
