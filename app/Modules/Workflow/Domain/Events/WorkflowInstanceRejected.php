<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\Events;

use App\Support\Events\BaseEvent;

final class WorkflowInstanceRejected extends BaseEvent
{
    public const string EVENT_NAME = 'workflow.request_approval.instance_rejected';

    public const string VERSION = '1.0';

    public static function forInstance(string $instanceId, string $requestId, string $stage, string $reason): self
    {
        return self::raise($instanceId, [
            'request_id' => $requestId,
            'stage' => $stage,
            'reason' => $reason,
        ]);
    }
}
