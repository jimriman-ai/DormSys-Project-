<?php

declare(strict_types=1);

use App\Modules\Allocation\Application\Contracts\AllocationReadContract;
use App\Modules\Allocation\Application\Services\AllocationReadService;

arch('allocation module does not import request infrastructure')
    ->expect('App\Modules\Allocation')
    ->not->toUse('App\Modules\Request\Infrastructure\*');

arch('allocation module does not import lottery infrastructure')
    ->expect('App\Modules\Allocation')
    ->not->toUse('App\Modules\Lottery\Infrastructure\*');

arch('allocation module does not import dormitory infrastructure')
    ->expect('App\Modules\Allocation')
    ->not->toUse('App\Modules\Dormitory\Infrastructure\*');

arch('allocation module does not import employee infrastructure')
    ->expect('App\Modules\Allocation')
    ->not->toUse('App\Modules\Employee\Infrastructure\*');

arch('allocation module does not import request persistence models')
    ->expect('App\Modules\Allocation')
    ->not->toUse('App\Modules\Request\Infrastructure\Persistence');

arch('allocation module does not import lottery persistence models')
    ->expect('App\Modules\Allocation')
    ->not->toUse('App\Modules\Lottery\Infrastructure\Persistence');

arch('allocation module does not import dormitory persistence models')
    ->expect('App\Modules\Allocation')
    ->not->toUse('App\Modules\Dormitory\Infrastructure\Persistence');

arch('allocation module does not import employee persistence models')
    ->expect('App\Modules\Allocation')
    ->not->toUse('App\Modules\Employee\Infrastructure\Persistence');

test('allocation read service is bound to the supplier read contract', function (): void {
    expect(app()->bound(AllocationReadContract::class))->toBeTrue()
        ->and(app(AllocationReadContract::class)::class)->toBe(AllocationReadService::class);
});
