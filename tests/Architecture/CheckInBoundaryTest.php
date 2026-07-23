<?php

declare(strict_types=1);

// DP-XMOD-BELONGS Option C: CheckIn Persistence Models may belongsTo Allocation Persistence Models.
arch('check-in module does not import allocation infrastructure eloquent models')
    ->expect('App\Modules\CheckIn')
    ->not->toUse('App\Modules\Allocation\Infrastructure\Persistence')
    ->ignoring(architectureOptionCForeignPersistenceModelAllowlist());

arch('check-in module does not import allocation infrastructure repositories')
    ->expect('App\Modules\CheckIn')
    ->not->toUse('App\Modules\Allocation\Infrastructure\Repositories');
