<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\DTOs;

final readonly class StartRequestApprovalWorkflowCommand
{
    public function __construct(
        public string $requestId,
        public ?string $stage1ApproverIdentityId,
    ) {}
}
