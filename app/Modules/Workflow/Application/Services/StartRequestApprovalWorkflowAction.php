<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\Services;

use App\Modules\Workflow\Application\Contracts\RequestApprovalWorkflowRepositoryContract;
use App\Modules\Workflow\Application\DTOs\StartRequestApprovalWorkflowCommand;
use App\Modules\Workflow\Application\DTOs\WorkflowInstanceResult;
use App\Modules\Workflow\Application\Support\WorkflowInstanceResultFactory;
use App\Modules\Workflow\Domain\Entities\RequestApprovalWorkflowInstance;
use App\Modules\Workflow\Domain\Enums\RequestApprovalWorkflowStage;
use App\Modules\Workflow\Domain\Events\WorkflowInstanceStarted;
use App\Modules\Workflow\Domain\Events\WorkflowStepActivated;
use App\Modules\Workflow\Domain\Exceptions\InvalidWorkflowTransitionException;
use App\Modules\Workflow\Domain\ValueObjects\IdentityUserId;
use App\Modules\Workflow\Domain\ValueObjects\RequestReferenceId;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\Event;

/**
 * Starts Request Approval Workflow on submit path (OD-1). Does not cut over Request module.
 */
final class StartRequestApprovalWorkflowAction
{
    public function __construct(
        private readonly RequestApprovalWorkflowRepositoryContract $workflows,
    ) {}

    public function execute(StartRequestApprovalWorkflowCommand $command): WorkflowInstanceResult
    {
        $requestId = RequestReferenceId::fromString($command->requestId);

        if ($this->workflows->findRunningByRequestId($requestId) !== null) {
            throw new InvalidWorkflowTransitionException('A running Request Approval Workflow already exists for this request.');
        }

        $stage1 = $command->stage1ApproverIdentityId !== null && $command->stage1ApproverIdentityId !== ''
            ? IdentityUserId::fromString($command->stage1ApproverIdentityId)
            : null;

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $instance = RequestApprovalWorkflowInstance::start($requestId, $stage1, $now);

        $this->workflows->save($instance);

        Event::dispatch(WorkflowInstanceStarted::forInstance(
            $instance->id->value,
            $instance->requestId->value,
            RequestApprovalWorkflowStage::DepartmentManager->value,
        ));
        Event::dispatch(WorkflowStepActivated::forStep(
            $instance->id->value,
            $instance->requestId->value,
            RequestApprovalWorkflowStage::DepartmentManager->value,
        ));

        return WorkflowInstanceResultFactory::fromInstance($instance);
    }
}
