<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Contracts;

use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Application\DTOs\AuditRecordResultDto;

interface AuditRecordingContract
{
    public function record(AuditEntryDto $entry): AuditRecordResultDto;
}
