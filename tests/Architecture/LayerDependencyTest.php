<?php

declare(strict_types=1);

// These rules are verified by ArchTestCanaryTest to prevent vacuous passes.

arch('domain layer does not depend on eloquent')
    ->expect('App\Modules\*\Domain')
    ->not->toUse('Illuminate\Database\Eloquent\*');

arch('domain layer does not depend on infrastructure')
    ->expect('App\Modules\*\Domain')
    ->not->toUse('App\Modules\*\Infrastructure');

arch('domain layer does not depend on presentation')
    ->expect('App\Modules\*\Domain')
    ->not->toUse('App\Modules\*\Presentation');

arch('domain layer does not depend on laravel facades')
    ->expect('App\Modules\*\Domain')
    ->not->toUse('Illuminate\Support\Facades');

arch('application layer does not depend on infrastructure')
    ->expect('App\Modules\*\Application')
    ->not->toUse('App\Modules\*\Infrastructure');

arch('application layer does not depend on presentation')
    ->expect('App\Modules\*\Application')
    ->not->toUse('App\Modules\*\Presentation');

arch('infrastructure layer does not depend on presentation')
    ->expect('App\Modules\*\Infrastructure')
    ->not->toUse('App\Modules\*\Presentation');

arch('shared kernel does not depend on modules')
    ->expect('App\Shared')
    ->not->toUse('App\Modules');

arch('support layer does not depend on modules')
    ->expect('App\Support')
    ->not->toUse('App\Modules');
