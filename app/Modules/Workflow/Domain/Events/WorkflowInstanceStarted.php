<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\Events;

use App\Support\Events\BaseEvent;

final class WorkflowInstanceStarted extends BaseEvent
{
    public const string EVENT_NAME = 'workflow.request_approval.instance_started';

    public const string VERSION = '1.0';

    public static function forInstance(string $instanceId, string $requestId, string $firstStage): self
    {
        return self::raise($instanceId, [
            'request_id' => $requestId,
            'first_stage' => $firstStage,
        ]);
    }
}
