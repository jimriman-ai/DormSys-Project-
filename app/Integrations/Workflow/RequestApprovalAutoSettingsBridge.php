<?php

declare(strict_types=1);

namespace App\Integrations\Workflow;

use App\Modules\Request\Application\Services\AutoApprovalSettingsReader;
use App\Modules\Request\Domain\Enums\ApprovalStage;
use App\Modules\Workflow\Application\Contracts\RequestApprovalAutoSettingsPort;
use App\Modules\Workflow\Domain\Enums\RequestApprovalWorkflowStage;

/** Adapts System settings auto-approval flags for Workflow (same keys as Request R-09). */
final class RequestApprovalAutoSettingsBridge implements RequestApprovalAutoSettingsPort
{
    public function __construct(
        private readonly AutoApprovalSettingsReader $autoApproval,
    ) {}

    public function isAutoApprovalEnabled(RequestApprovalWorkflowStage $stage): bool
    {
        $requestStage = ApprovalStage::tryFrom($stage->value);

        if ($requestStage === null) {
            return false;
        }

        return $this->autoApproval->isEnabled($requestStage);
    }
}
