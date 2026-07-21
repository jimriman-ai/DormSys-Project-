<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Workflow\Support;

use App\Modules\Workflow\Application\Contracts\RequestApprovalWorkflowRepositoryContract;
use App\Modules\Workflow\Domain\Entities\RequestApprovalWorkflowInstance;
use App\Modules\Workflow\Domain\Enums\WorkflowInstanceStatus;
use App\Modules\Workflow\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Workflow\Domain\ValueObjects\WorkflowInstanceId;

final class InMemoryRequestApprovalWorkflowRepository implements RequestApprovalWorkflowRepositoryContract
{
    /** @var array<string, RequestApprovalWorkflowInstance> */
    private array $byId = [];

    public function save(RequestApprovalWorkflowInstance $instance): void
    {
        $this->byId[$instance->id->value] = $instance;
    }

    public function findById(WorkflowInstanceId $id): ?RequestApprovalWorkflowInstance
    {
        return $this->byId[$id->value] ?? null;
    }

    public function findRunningByRequestId(RequestReferenceId $requestId): ?RequestApprovalWorkflowInstance
    {
        foreach ($this->byId as $instance) {
            if (
                $instance->requestId->value === $requestId->value
                && $instance->status === WorkflowInstanceStatus::Running
            ) {
                return $instance;
            }
        }

        return null;
    }
}
