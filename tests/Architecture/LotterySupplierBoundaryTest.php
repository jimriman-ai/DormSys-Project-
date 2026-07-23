<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Adapters\RequestReadAdapter;
use App\Modules\Lottery\Application\Contracts\LotteryRequestReadPort;
use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Application\Services\LotteryResultReadService;

// DP-XMOD-BELONGS Option C: foreign Persistence Models allowed for read belongsTo.
$optionC = architectureOptionCForeignPersistenceModelAllowlist();

arch('lottery module does not import request infrastructure (SC-005)')
    ->expect('App\Modules\Lottery')
    ->not->toUse('App\Modules\Request\Infrastructure\*')
    ->ignoring($optionC);

arch('lottery module does not import employee infrastructure (SC-005)')
    ->expect('App\Modules\Lottery')
    ->not->toUse('App\Modules\Employee\Infrastructure\*')
    ->ignoring($optionC);

arch('lottery module does not import dormitory infrastructure (SC-005)')
    ->expect('App\Modules\Lottery')
    ->not->toUse('App\Modules\Dormitory\Infrastructure\*')
    ->ignoring($optionC);

arch('lottery module does not import allocation infrastructure (SC-005)')
    ->expect('App\Modules\Lottery')
    ->not->toUse('App\Modules\Allocation\Infrastructure\*')
    ->ignoring($optionC);

// Persistence-model import bans removed: contradicted Option C allowlist.

arch('lottery domain does not import request modules (SC-005)')
    ->expect('App\Modules\Lottery\Domain')
    ->not->toUse('App\Modules\Request\*');

arch('lottery domain does not import employee modules (SC-005)')
    ->expect('App\Modules\Lottery\Domain')
    ->not->toUse('App\Modules\Employee\*');

arch('lottery domain does not import dormitory modules (SC-005)')
    ->expect('App\Modules\Lottery\Domain')
    ->not->toUse('App\Modules\Dormitory\*');

arch('lottery domain does not import allocation modules (SC-005)')
    ->expect('App\Modules\Lottery\Domain')
    ->not->toUse('App\Modules\Allocation\*');

arch('lottery infrastructure does not import request domain layers (SC-005)')
    ->expect('App\Modules\Lottery\Infrastructure')
    ->not->toUse('App\Modules\Request\Domain\*');

arch('lottery infrastructure does not import employee domain layers (SC-005)')
    ->expect('App\Modules\Lottery\Infrastructure')
    ->not->toUse('App\Modules\Employee\Domain\*');

arch('lottery infrastructure does not import dormitory domain layers (SC-005)')
    ->expect('App\Modules\Lottery\Infrastructure')
    ->not->toUse('App\Modules\Dormitory\Domain\*');

arch('lottery infrastructure does not import allocation domain layers (SC-005)')
    ->expect('App\Modules\Lottery\Infrastructure')
    ->not->toUse('App\Modules\Allocation\Domain\*');

test('request read adapter implements only the lottery request read port (R4)', function (): void {
    $adapterReflection = new ReflectionClass(RequestReadAdapter::class);
    $portReflection = new ReflectionClass(LotteryRequestReadPort::class);

    expect($adapterReflection->implementsInterface(LotteryRequestReadPort::class))->toBeTrue();

    $adapterMethods = array_values(array_filter(
        $adapterReflection->getMethods(ReflectionMethod::IS_PUBLIC),
        static fn (ReflectionMethod $method): bool => ! $method->isConstructor() && ! $method->isStatic(),
    ));

    $portMethods = array_values(array_filter(
        $portReflection->getMethods(ReflectionMethod::IS_PUBLIC),
        static fn (ReflectionMethod $method): bool => ! $method->isConstructor() && ! $method->isStatic(),
    ));

    expect(array_map(static fn (ReflectionMethod $method): string => $method->getName(), $adapterMethods))
        ->toEqual(array_map(static fn (ReflectionMethod $method): string => $method->getName(), $portMethods));
});

test('lottery result read service is bound to the supplier read contract', function (): void {
    expect(app()->bound(LotteryResultReadContract::class))->toBeTrue()
        ->and(app(LotteryResultReadContract::class)::class)->toBe(LotteryResultReadService::class);
});
