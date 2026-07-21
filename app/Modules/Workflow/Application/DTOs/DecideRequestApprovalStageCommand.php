<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\DTOs;

final readonly class DecideRequestApprovalStageCommand
{
    public function __construct(
        public string $requestId,
        public string $actorIdentityId,
        public string $decision,
        public ?string $reason = null,
    ) {}
}
