<?php

declare(strict_types=1);

arch('employee module does not import identity infrastructure')
    ->expect('App\Modules\Employee')
    ->not->toUse('App\Modules\Identity\Infrastructure\*');

arch('employee module does not import identity persistence models')
    ->expect('App\Modules\Employee')
    ->not->toUse('App\Modules\Identity\Infrastructure\Persistence\Models\UserModel');
