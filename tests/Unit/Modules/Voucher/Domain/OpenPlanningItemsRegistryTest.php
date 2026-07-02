<?php

declare(strict_types=1);

use App\Modules\Voucher\Domain\OpenPlanningItemsRegistry;

it('carries forward UD-03 as open per plan open-questions registry', function (): void {
    $items = OpenPlanningItemsRegistry::carryForwardItems();

    expect($items)->toHaveKey(OpenPlanningItemsRegistry::UD_03);
    expect($items[OpenPlanningItemsRegistry::UD_03]['status'])->toBe('open');
    expect(OpenPlanningItemsRegistry::isOpen(OpenPlanningItemsRegistry::UD_03))->toBeTrue();
});

it('carries forward UD-08 as open per plan open-questions registry', function (): void {
    $items = OpenPlanningItemsRegistry::carryForwardItems();

    expect($items)->toHaveKey(OpenPlanningItemsRegistry::UD_08);
    expect($items[OpenPlanningItemsRegistry::UD_08]['status'])->toBe('open');
    expect(OpenPlanningItemsRegistry::isOpen(OpenPlanningItemsRegistry::UD_08))->toBeTrue();
});

it('does not resolve open planning items at implementation closure', function (): void {
    $openCount = count(array_filter(
        OpenPlanningItemsRegistry::carryForwardItems(),
        fn (array $item): bool => $item['status'] === 'open',
    ));

    expect($openCount)->toBe(2);
});
