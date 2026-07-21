<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\Support;

use App\Modules\Workflow\Application\DTOs\WorkflowInstanceResult;
use App\Modules\Workflow\Domain\Entities\RequestApprovalWorkflowInstance;

final class WorkflowInstanceResultFactory
{
    public static function fromInstance(RequestApprovalWorkflowInstance $instance): WorkflowInstanceResult
    {
        return new WorkflowInstanceResult(
            instanceId: $instance->id->value,
            requestId: $instance->requestId->value,
            status: $instance->status->value,
            currentStage: $instance->currentStage?->value,
        );
    }
}
