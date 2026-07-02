<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Services;

/**
 * Strips physical assignment identifiers from lottery winner facts (BR-12).
 */
final class ExternalLotteryWinnerFactsSanitizer
{
    /**
     * @var list<string>
     */
    private const array STRIPPED_KEYS = [
        'room_id',
        'bed_id',
        'room_identifier',
        'bed_identifier',
        'room_number',
        'bed_number',
    ];

    /**
     * @param  array<string, mixed>  $facts
     * @return array<string, mixed>
     */
    public function sanitize(array $facts): array
    {
        $sanitized = $facts;

        foreach (self::STRIPPED_KEYS as $key) {
            unset($sanitized[$key]);
        }

        return $sanitized;
    }
}
