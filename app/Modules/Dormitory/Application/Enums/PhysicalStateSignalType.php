<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Enums;

enum PhysicalStateSignalType: string
{
    case Reserve = 'reserve';
    case OccupyMarker = 'occupy_marker';
    case Release = 'release';
}
