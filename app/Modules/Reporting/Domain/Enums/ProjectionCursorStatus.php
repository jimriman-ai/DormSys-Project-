<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Domain\Enums;

enum ProjectionCursorStatus: string
{
    case Idle = 'idle';
    case Running = 'running';
    case Failed = 'failed';
}
