<?php

declare(strict_types=1);

use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;

arch('voucher module does not import allocation infrastructure')
    ->expect('App\Modules\Voucher')
    ->not->toUse('App\Modules\Allocation\Infrastructure\*');

arch('voucher module does not import lottery infrastructure')
    ->expect('App\Modules\Voucher')
    ->not->toUse('App\Modules\Lottery\Infrastructure\*');

arch('voucher module does not import request infrastructure')
    ->expect('App\Modules\Voucher')
    ->not->toUse('App\Modules\Request\Infrastructure\*');

arch('voucher module does not import allocation persistence models')
    ->expect('App\Modules\Voucher')
    ->not->toUse('App\Modules\Allocation\Infrastructure\Persistence');

arch('voucher module does not import lottery persistence models')
    ->expect('App\Modules\Voucher')
    ->not->toUse('App\Modules\Lottery\Infrastructure\Persistence');

test('voucher trigger intake contract is bound', function (): void {
    expect(app(VoucherTriggerIntakeContract::class))->toBeInstanceOf(VoucherTriggerIntakeContract::class);
});
