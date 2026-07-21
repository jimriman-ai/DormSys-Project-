<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\Services;

use App\Modules\Workflow\Application\Contracts\RequestApprovalAutoSettingsPort;
use App\Modules\Workflow\Application\Contracts\RequestApprovalCommandPort;
use App\Modules\Workflow\Application\Contracts\RequestApprovalWorkflowRepositoryContract;
use App\Modules\Workflow\Application\DTOs\WorkflowInstanceResult;
use App\Modules\Workflow\Application\Support\WorkflowInstanceResultFactory;
use App\Modules\Workflow\Application\Support\WorkflowSystemActor;
use App\Modules\Workflow\Domain\Enums\WorkflowInstanceStatus;
use App\Modules\Workflow\Domain\Enums\WorkflowStepStatus;
use App\Modules\Workflow\Domain\Events\WorkflowInstanceCompleted;
use App\Modules\Workflow\Domain\Events\WorkflowStepActivated;
use App\Modules\Workflow\Domain\Events\WorkflowStepCompleted;
use App\Modules\Workflow\Domain\Exceptions\WorkflowInstanceNotFoundException;
use App\Modules\Workflow\Domain\ValueObjects\IdentityUserId;
use App\Modules\Workflow\Domain\ValueObjects\RequestReferenceId;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\Event;

/**
 * Applies consecutive auto-approvals while settings enable the current stage (OD-3 audit = SkippedAuto).
 */
final class ApplyRequestApprovalAutoApprovalsAction
{
    public function __construct(
        private readonly RequestApprovalWorkflowRepositoryContract $workflows,
        private readonly RequestApprovalCommandPort $requestApprovals,
        private readonly RequestApprovalAutoSettingsPort $autoSettings,
    ) {}

    public function execute(string $requestId): WorkflowInstanceResult
    {
        $ref = RequestReferenceId::fromString($requestId);
        $instance = $this->workflows->findRunningByRequestId($ref);

        if ($instance === null) {
            throw new WorkflowInstanceNotFoundException('No running Request Approval Workflow for request.');
        }

        $systemActor = IdentityUserId::fromString(WorkflowSystemActor::IDENTITY_ID);
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        while (
            $instance->status === WorkflowInstanceStatus::Running
            && $instance->currentStage !== null
            && $this->autoSettings->isAutoApprovalEnabled($instance->currentStage)
        ) {
            $result = $instance->autoApprove($systemActor, $now);
            $completed = $result['completedStep'];

            $this->requestApprovals->recordStageApproved(
                $instance->requestId->value,
                $completed->stage->value,
                $systemActor->value,
                $now,
            );

            Event::dispatch(WorkflowStepCompleted::forStep(
                $instance->id->value,
                $instance->requestId->value,
                $completed->stage->value,
                WorkflowStepStatus::SkippedAuto->value,
                $systemActor->value,
            ));

            if ($result['activatedStage'] !== null) {
                Event::dispatch(WorkflowStepActivated::forStep(
                    $instance->id->value,
                    $instance->requestId->value,
                    $result['activatedStage']->value,
                ));
            }

            // Final stage leaves no activated successor (status becomes Completed in domain).
            if ($result['activatedStage'] === null) {
                Event::dispatch(WorkflowInstanceCompleted::forInstance(
                    $instance->id->value,
                    $instance->requestId->value,
                ));
                break;
            }
        }

        $this->workflows->save($instance);

        return WorkflowInstanceResultFactory::fromInstance($instance);
    }
}
