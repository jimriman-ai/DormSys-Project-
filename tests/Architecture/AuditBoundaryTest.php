<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditHistoryReadContract;
use App\Modules\Audit\Application\Contracts\AuditRecordingContract;

arch('audit module does not import request infrastructure')
    ->expect('App\Modules\Audit')
    ->not->toUse('App\Modules\Request\Infrastructure\*');

arch('audit module does not import lottery infrastructure')
    ->expect('App\Modules\Audit')
    ->not->toUse('App\Modules\Lottery\Infrastructure\*');

arch('audit module does not import allocation infrastructure')
    ->expect('App\Modules\Audit')
    ->not->toUse('App\Modules\Allocation\Infrastructure\*');

arch('audit module does not import voucher infrastructure')
    ->expect('App\Modules\Audit')
    ->not->toUse('App\Modules\Voucher\Infrastructure\*');

arch('audit module does not import check-in infrastructure')
    ->expect('App\Modules\Audit')
    ->not->toUse('App\Modules\CheckIn\Infrastructure\*');

arch('audit module does not import notification infrastructure')
    ->expect('App\Modules\Audit')
    ->not->toUse('App\Modules\Notification\Infrastructure\*');

arch('audit module does not import request persistence models')
    ->expect('App\Modules\Audit')
    ->not->toUse('App\Modules\Request\Infrastructure\Persistence');

arch('audit module does not import lottery persistence models')
    ->expect('App\Modules\Audit')
    ->not->toUse('App\Modules\Lottery\Infrastructure\Persistence');

arch('audit module does not import allocation persistence models')
    ->expect('App\Modules\Audit')
    ->not->toUse('App\Modules\Allocation\Infrastructure\Persistence');

arch('audit module does not import voucher persistence models')
    ->expect('App\Modules\Audit')
    ->not->toUse('App\Modules\Voucher\Infrastructure\Persistence');

arch('audit module does not import check-in persistence models')
    ->expect('App\Modules\Audit')
    ->not->toUse('App\Modules\CheckIn\Infrastructure\Persistence');

arch('audit module does not import notification persistence models')
    ->expect('App\Modules\Audit')
    ->not->toUse('App\Modules\Notification\Infrastructure\Persistence');

test('audit recording is bound to the module recording contract', function (): void {
    app(AuditRecordingContract::class);
});

test('audit history read is bound to the module read service', function (): void {
    app(AuditHistoryReadContract::class);
});
