<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\DTOs;

final readonly class WorkflowInstanceResult
{
    public function __construct(
        public string $instanceId,
        public string $requestId,
        public string $status,
        public ?string $currentStage,
    ) {}
}
