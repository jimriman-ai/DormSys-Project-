<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Domain\Enums\AccommodationClassification;

interface AccommodationClassificationReadPort
{
    public function getClassification(string $dormitoryId): ?AccommodationClassification;
}
