<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Infrastructure\Persistence\Models;

use App\Modules\Workflow\Domain\Enums\RequestApprovalWorkflowStage;
use App\Modules\Workflow\Domain\Enums\WorkflowInstanceStatus;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Persistence model for Request Approval Workflow instance (orchestration only).
 *
 * @property string $id
 * @property string $request_id
 * @property WorkflowInstanceStatus $status
 * @property string|null $stage1_approver_identity_id
 * @property RequestApprovalWorkflowStage|null $current_stage
 * @property Carbon $started_at
 * @property Carbon|null $completed_at
 */
class RequestApprovalWorkflowInstanceModel extends Model
{
    use HasUuid;

    protected $table = 'workflow_request_approval_instances';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'request_id',
        'status',
        'stage1_approver_identity_id',
        'current_stage',
        'started_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => WorkflowInstanceStatus::class,
            'current_stage' => RequestApprovalWorkflowStage::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<RequestApprovalWorkflowStepExecutionModel, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(
            RequestApprovalWorkflowStepExecutionModel::class,
            'workflow_instance_id',
        )->orderBy('activated_at')->orderBy('id');
    }
}
