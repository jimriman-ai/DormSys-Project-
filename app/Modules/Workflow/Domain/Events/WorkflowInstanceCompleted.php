<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\Events;

use App\Support\Events\BaseEvent;

final class WorkflowInstanceCompleted extends BaseEvent
{
    public const string EVENT_NAME = 'workflow.request_approval.instance_completed';

    public const string VERSION = '1.0';

    public static function forInstance(string $instanceId, string $requestId): self
    {
        return self::raise($instanceId, [
            'request_id' => $requestId,
        ]);
    }
}
