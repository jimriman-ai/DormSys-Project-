<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use DateTimeImmutable;
use DateTimeZone;

final class ProjectionDayWindowResolver
{
    /**
     * @return array{windowStart: DateTimeImmutable, windowEnd: DateTimeImmutable}
     */
    public function resolve(DateTimeImmutable $occurredAt): array
    {
        $utc = $occurredAt->setTimezone(new DateTimeZone('UTC'));
        $windowStart = new DateTimeImmutable(
            $utc->format('Y-m-d').' 00:00:00',
            new DateTimeZone('UTC'),
        );

        return [
            'windowStart' => $windowStart,
            'windowEnd' => $windowStart->modify('+1 day'),
        ];
    }
}
