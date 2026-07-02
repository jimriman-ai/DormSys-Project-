<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Enums;

enum AccommodationClassification: string
{
    case External = 'external';
    case Internal = 'internal';
}
