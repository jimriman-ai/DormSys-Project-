<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\Contracts;

use App\Modules\Workflow\Domain\Enums\RequestApprovalWorkflowStage;

/** Settings read port for auto-approval flags (System SettingsReadContract adapter later). */
interface RequestApprovalAutoSettingsPort
{
    public function isAutoApprovalEnabled(RequestApprovalWorkflowStage $stage): bool;
}
