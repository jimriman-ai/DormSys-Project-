<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\Contracts;

use App\Modules\Workflow\Domain\Entities\RequestApprovalWorkflowInstance;
use App\Modules\Workflow\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Workflow\Domain\ValueObjects\WorkflowInstanceId;

/**
 * Persistence port — infrastructure implements in WP-WF-03 (no Eloquent here).
 */
interface RequestApprovalWorkflowRepositoryContract
{
    public function save(RequestApprovalWorkflowInstance $instance): void;

    public function findById(WorkflowInstanceId $id): ?RequestApprovalWorkflowInstance;

    public function findRunningByRequestId(RequestReferenceId $requestId): ?RequestApprovalWorkflowInstance;
}
