<?php

declare(strict_types=1);

namespace App\Modules\Audit\Domain\Enums;

enum AuditRecordStatus: string
{
    case Created = 'created';
    case Duplicate = 'duplicate';
}
