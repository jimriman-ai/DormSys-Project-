<?php

declare(strict_types=1);

arch('check-in module does not import allocation infrastructure eloquent models')
    ->expect('App\Modules\CheckIn')
    ->not->toUse('App\Modules\Allocation\Infrastructure\Persistence');

arch('check-in module does not import allocation infrastructure repositories')
    ->expect('App\Modules\CheckIn')
    ->not->toUse('App\Modules\Allocation\Infrastructure\Repositories');
