<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Services;

use App\Modules\Request\Domain\Enums\ApprovalStage;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryUnitState;
use App\Modules\Request\Domain\States\PendingHRState;

final class ApprovalStageResolver
{
    public function stageForStatus(string $status): ?ApprovalStage
    {
        return match ($status) {
            PendingDepartmentManagerState::$name => ApprovalStage::DepartmentManager,
            PendingHRState::$name => ApprovalStage::HR,
            PendingDormitoryManagerState::$name => ApprovalStage::DormitoryManager,
            PendingDormitoryUnitState::$name => ApprovalStage::DormitoryUnit,
            default => null,
        };
    }

    public function statusAfterApproval(ApprovalStage $stage): string
    {
        return match ($stage) {
            ApprovalStage::DepartmentManager => PendingHRState::$name,
            ApprovalStage::HR => PendingDormitoryManagerState::$name,
            ApprovalStage::DormitoryManager => PendingDormitoryUnitState::$name,
            ApprovalStage::DormitoryUnit => ApprovedState::$name,
        };
    }

    public function isPendingApprovalStatus(string $status): bool
    {
        return $this->stageForStatus($status) !== null;
    }
}
