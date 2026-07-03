<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain;

/**
 * Carry-forward registry for spec08 open planning items (T031).
 * Aligns with plan.md open-questions registry — items remain unresolved at implementation closure.
 */
final class OpenPlanningItemsRegistry
{
    public const string UD_03 = 'UD-03';

    public const string UD_08 = 'UD-08';

    /**
     * @return array<string, array{id: string, status: string, summary: string}>
     */
    public static function carryForwardItems(): array
    {
        return [
            self::UD_03 => [
                'id' => self::UD_03,
                'status' => 'open',
                'summary' => 'Upstream trigger fact bundle shape (lottery winner facts; allocation-related triggers)',
            ],
            self::UD_08 => [
                'id' => self::UD_08,
                'status' => 'open',
                'summary' => 'Voucher expiration and renewal policy detail',
            ],
        ];
    }

    public static function isOpen(string $itemId): bool
    {
        $items = self::carryForwardItems();

        return isset($items[$itemId]) && $items[$itemId]['status'] === 'open';
    }
}
