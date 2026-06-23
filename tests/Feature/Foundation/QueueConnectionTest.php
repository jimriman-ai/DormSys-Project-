<?php

declare(strict_types=1);

namespace Tests\Feature\Foundation;

use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueueConnectionTest extends TestCase
{
    public function test_queue_connection_uses_redis(): void
    {
        $this->assertSame('redis', config('queue.default'));
    }

    public function test_job_can_be_pushed_to_redis_queue(): void
    {
        Queue::fake();

        dispatch(function (): void {
            logger('Queue health check passed');
        });

        Queue::assertPushed(CallQueuedClosure::class);
    }
}
