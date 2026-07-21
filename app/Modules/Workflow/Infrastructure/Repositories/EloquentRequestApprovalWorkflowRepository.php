<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Infrastructure\Repositories;

use App\Modules\Workflow\Application\Contracts\RequestApprovalWorkflowRepositoryContract;
use App\Modules\Workflow\Domain\Entities\RequestApprovalWorkflowInstance;
use App\Modules\Workflow\Domain\Entities\WorkflowStepExecution;
use App\Modules\Workflow\Domain\Enums\WorkflowInstanceStatus;
use App\Modules\Workflow\Domain\ValueObjects\IdentityUserId;
use App\Modules\Workflow\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Workflow\Domain\ValueObjects\WorkflowInstanceId;
use App\Modules\Workflow\Domain\ValueObjects\WorkflowStepId;
use App\Modules\Workflow\Infrastructure\Persistence\Models\RequestApprovalWorkflowInstanceModel;
use App\Modules\Workflow\Infrastructure\Persistence\Models\RequestApprovalWorkflowStepExecutionModel;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class EloquentRequestApprovalWorkflowRepository implements RequestApprovalWorkflowRepositoryContract
{
    public function save(RequestApprovalWorkflowInstance $instance): void
    {
        DB::transaction(function () use ($instance): void {
            $model = RequestApprovalWorkflowInstanceModel::query()->find($instance->id->value);

            if ($model === null) {
                $model = new RequestApprovalWorkflowInstanceModel;
                $model->id = $instance->id->value;
            }

            $model->fill([
                'request_id' => $instance->requestId->value,
                'status' => $instance->status,
                'stage1_approver_identity_id' => $instance->stage1ApproverIdentityId?->value,
                'current_stage' => $instance->currentStage,
                'started_at' => $instance->startedAt,
                'completed_at' => $instance->completedAt,
            ]);
            $model->save();

            foreach ($instance->steps as $step) {
                $stepModel = RequestApprovalWorkflowStepExecutionModel::query()->find($step->id->value);

                if ($stepModel === null) {
                    $stepModel = new RequestApprovalWorkflowStepExecutionModel;
                    $stepModel->id = $step->id->value;
                }

                $stepModel->fill([
                    'workflow_instance_id' => $instance->id->value,
                    'stage' => $step->stage,
                    'status' => $step->status,
                    'actor_identity_id' => $step->actorId?->value,
                    'reason' => $step->reason,
                    'activated_at' => $step->activatedAt,
                    'completed_at' => $step->completedAt,
                ]);
                $stepModel->save();
            }
        });
    }

    public function findById(WorkflowInstanceId $id): ?RequestApprovalWorkflowInstance
    {
        $model = RequestApprovalWorkflowInstanceModel::query()
            ->with(['steps' => static function ($query): void {
                $query->orderBy('activated_at')->orderBy('id');
            }])
            ->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findRunningByRequestId(RequestReferenceId $requestId): ?RequestApprovalWorkflowInstance
    {
        $model = RequestApprovalWorkflowInstanceModel::query()
            ->with(['steps' => static function ($query): void {
                $query->orderBy('activated_at')->orderBy('id');
            }])
            ->where('request_id', $requestId->value)
            ->where('status', WorkflowInstanceStatus::Running)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    private function toDomain(RequestApprovalWorkflowInstanceModel $model): RequestApprovalWorkflowInstance
    {
        $steps = [];

        foreach ($model->steps as $stepModel) {
            $steps[] = new WorkflowStepExecution(
                id: WorkflowStepId::fromString($stepModel->id),
                stage: $stepModel->stage,
                status: $stepModel->status,
                actorId: $stepModel->actor_identity_id !== null
                    ? IdentityUserId::fromString($stepModel->actor_identity_id)
                    : null,
                reason: $stepModel->reason,
                activatedAt: DateTimeImmutable::createFromInterface($stepModel->activated_at),
                completedAt: $stepModel->completed_at !== null
                    ? DateTimeImmutable::createFromInterface($stepModel->completed_at)
                    : null,
            );
        }

        return new RequestApprovalWorkflowInstance(
            id: WorkflowInstanceId::fromString($model->id),
            requestId: RequestReferenceId::fromString($model->request_id),
            status: $model->status,
            stage1ApproverIdentityId: $model->stage1_approver_identity_id !== null
                ? IdentityUserId::fromString($model->stage1_approver_identity_id)
                : null,
            currentStage: $model->current_stage,
            steps: $steps,
            startedAt: DateTimeImmutable::createFromInterface($model->started_at),
            completedAt: $model->completed_at !== null
                ? DateTimeImmutable::createFromInterface($model->completed_at)
                : null,
        );
    }
}
