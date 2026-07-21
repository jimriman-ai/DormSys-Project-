<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\Services;

use App\Modules\Workflow\Domain\Enums\RequestApprovalWorkflowStage;

/**
 * OD-2: fixed, policy-controlled stage → role map (not admin-configurable).
 */
final class FixedRequestApprovalStageRolePolicy
{
    public function roleFor(RequestApprovalWorkflowStage $stage): string
    {
        return match ($stage) {
            RequestApprovalWorkflowStage::DepartmentManager,
            RequestApprovalWorkflowStage::DormitoryManager => 'dormitory-manager',
            RequestApprovalWorkflowStage::HR => 'HR',
            RequestApprovalWorkflowStage::DormitoryUnit => 'dormitory-unit',
        };
    }
}
