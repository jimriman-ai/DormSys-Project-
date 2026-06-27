<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Enums;

enum ApprovalDecision: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Pending = 'pending';
}
