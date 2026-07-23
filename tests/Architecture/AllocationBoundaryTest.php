<?php

declare(strict_types=1);

use App\Modules\Allocation\Application\Contracts\AllocationReadContract;
use App\Modules\Allocation\Application\Services\AllocationReadService;

// DP-XMOD-BELONGS Option C: foreign Persistence Models allowed for read belongsTo.
$optionC = architectureOptionCForeignPersistenceModelAllowlist();

arch('allocation module does not import request infrastructure')
    ->expect('App\Modules\Allocation')
    ->not->toUse('App\Modules\Request\Infrastructure\*')
    ->ignoring($optionC);

arch('allocation module does not import lottery infrastructure')
    ->expect('App\Modules\Allocation')
    ->not->toUse('App\Modules\Lottery\Infrastructure\*')
    ->ignoring($optionC);

arch('allocation module does not import dormitory infrastructure')
    ->expect('App\Modules\Allocation')
    ->not->toUse('App\Modules\Dormitory\Infrastructure\*')
    ->ignoring($optionC);

arch('allocation module does not import employee infrastructure')
    ->expect('App\Modules\Allocation')
    ->not->toUse('App\Modules\Employee\Infrastructure\*')
    ->ignoring($optionC);

// Persistence-model import bans removed: contradicted Option C allowlist (same as Infrastructure\* + ignoring).

test('allocation read service is bound to the supplier read contract', function (): void {
    expect(app()->bound(AllocationReadContract::class))->toBeTrue()
        ->and(app(AllocationReadContract::class)::class)->toBe(AllocationReadService::class);
});
