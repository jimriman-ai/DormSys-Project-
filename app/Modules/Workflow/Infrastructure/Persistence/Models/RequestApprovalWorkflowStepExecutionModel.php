<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Infrastructure\Persistence\Models;

use App\Modules\Workflow\Domain\Enums\RequestApprovalWorkflowStage;
use App\Modules\Workflow\Domain\Enums\WorkflowStepStatus;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Persistence model for orchestration step audit (OD-3 — non-canonical product history).
 *
 * @property string $id
 * @property string $workflow_instance_id
 * @property RequestApprovalWorkflowStage $stage
 * @property WorkflowStepStatus $status
 * @property string|null $actor_identity_id
 * @property string|null $reason
 * @property Carbon $activated_at
 * @property Carbon|null $completed_at
 */
class RequestApprovalWorkflowStepExecutionModel extends Model
{
    use HasUuid;

    protected $table = 'workflow_request_approval_step_executions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'workflow_instance_id',
        'stage',
        'status',
        'actor_identity_id',
        'reason',
        'activated_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stage' => RequestApprovalWorkflowStage::class,
            'status' => WorkflowStepStatus::class,
            'activated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<RequestApprovalWorkflowInstanceModel, $this>
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(
            RequestApprovalWorkflowInstanceModel::class,
            'workflow_instance_id',
        );
    }
}
