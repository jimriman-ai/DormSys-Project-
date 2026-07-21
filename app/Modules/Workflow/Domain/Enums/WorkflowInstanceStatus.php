<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\Enums;

enum WorkflowInstanceStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
}
