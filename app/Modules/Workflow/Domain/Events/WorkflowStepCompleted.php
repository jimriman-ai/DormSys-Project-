<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\Events;

use App\Support\Events\BaseEvent;

final class WorkflowStepCompleted extends BaseEvent
{
    public const string EVENT_NAME = 'workflow.request_approval.step_completed';

    public const string VERSION = '1.0';

    public static function forStep(
        string $instanceId,
        string $requestId,
        string $stage,
        string $stepStatus,
        string $actorId,
    ): self {
        return self::raise($instanceId, [
            'request_id' => $requestId,
            'stage' => $stage,
            'step_status' => $stepStatus,
            'actor_id' => $actorId,
        ]);
    }
}
