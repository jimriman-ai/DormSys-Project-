<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Adapters;

use App\Modules\Voucher\Application\Contracts\AccommodationClassificationReadPort;
use App\Modules\Voucher\Domain\Enums\AccommodationClassification;

/**
 * Stub supplier until spec04 accommodation catalog is live (OA-08-02).
 */
final class StubAccommodationClassificationReadAdapter implements AccommodationClassificationReadPort
{
    public function getClassification(string $dormitoryId): ?AccommodationClassification
    {
        return null;
    }
}
