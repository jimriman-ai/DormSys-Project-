<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Domain\Enums;

enum RefreshMode: string
{
    case Incremental = 'incremental';
    case WindowSnapshot = 'window_snapshot';
    case FullRebuild = 'full_rebuild';
}
