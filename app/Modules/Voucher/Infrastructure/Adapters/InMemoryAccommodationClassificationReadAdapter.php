<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Adapters;

use App\Modules\Voucher\Application\Contracts\AccommodationClassificationReadPort;
use App\Modules\Voucher\Domain\Enums\AccommodationClassification;

final class InMemoryAccommodationClassificationReadAdapter implements AccommodationClassificationReadPort
{
    /**
     * @param  array<string, AccommodationClassification>  $classifications
     */
    public function __construct(
        private array $classifications = [],
    ) {}

    public function getClassification(string $dormitoryId): ?AccommodationClassification
    {
        return $this->classifications[$dormitoryId] ?? null;
    }
}
