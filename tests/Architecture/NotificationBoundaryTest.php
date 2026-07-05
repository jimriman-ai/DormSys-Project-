<?php

declare(strict_types=1);

use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;

arch('notification module does not import request infrastructure')
    ->expect('App\Modules\Notification')
    ->not->toUse('App\Modules\Request\Infrastructure\*');

arch('notification module does not import lottery infrastructure')
    ->expect('App\Modules\Notification')
    ->not->toUse('App\Modules\Lottery\Infrastructure\*');

arch('notification module does not import allocation infrastructure')
    ->expect('App\Modules\Notification')
    ->not->toUse('App\Modules\Allocation\Infrastructure\*');

arch('notification module does not import voucher infrastructure')
    ->expect('App\Modules\Notification')
    ->not->toUse('App\Modules\Voucher\Infrastructure\*');

arch('notification module does not import check-in infrastructure')
    ->expect('App\Modules\Notification')
    ->not->toUse('App\Modules\CheckIn\Infrastructure\*');

arch('notification module does not import request persistence models')
    ->expect('App\Modules\Notification')
    ->not->toUse('App\Modules\Request\Infrastructure\Persistence');

arch('notification module does not import lottery persistence models')
    ->expect('App\Modules\Notification')
    ->not->toUse('App\Modules\Lottery\Infrastructure\Persistence');

arch('notification module does not import allocation persistence models')
    ->expect('App\Modules\Notification')
    ->not->toUse('App\Modules\Allocation\Infrastructure\Persistence');

arch('notification module does not import voucher persistence models')
    ->expect('App\Modules\Notification')
    ->not->toUse('App\Modules\Voucher\Infrastructure\Persistence');

arch('notification module does not import check-in persistence models')
    ->expect('App\Modules\Notification')
    ->not->toUse('App\Modules\CheckIn\Infrastructure\Persistence');

test('notification delivery is bound to the module delivery contract', function (): void {
    app(NotificationDeliveryContract::class);
});

test('notification inbox read depends only on notification contracts', function (): void {
    app(NotificationInboxReadContract::class);
});
