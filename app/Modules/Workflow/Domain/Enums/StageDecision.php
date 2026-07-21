<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\Enums;

enum StageDecision: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
}
