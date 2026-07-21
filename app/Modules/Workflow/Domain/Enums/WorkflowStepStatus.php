<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\Enums;

/**
 * Orchestration audit statuses for a step (OD-3 — non-canonical product history).
 */
enum WorkflowStepStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case SkippedAuto = 'skipped_auto';
    case Cancelled = 'cancelled';
}
