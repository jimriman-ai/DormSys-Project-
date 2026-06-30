<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Adapters\RequestReadAdapter;
use App\Modules\Lottery\Application\Contracts\LotteryRequestReadPort;
use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Application\Services\LotteryResultReadService;

arch('lottery module does not import request infrastructure (SC-005)')
    ->expect('App\Modules\Lottery')
    ->not->toUse('App\Modules\Request\Infrastructure\*');

arch('lottery module does not import employee infrastructure (SC-005)')
    ->expect('App\Modules\Lottery')
    ->not->toUse('App\Modules\Employee\Infrastructure\*');

arch('lottery module does not import dormitory infrastructure (SC-005)')
    ->expect('App\Modules\Lottery')
    ->not->toUse('App\Modules\Dormitory\Infrastructure\*');

arch('lottery module does not import allocation infrastructure (SC-005)')
    ->expect('App\Modules\Lottery')
    ->not->toUse('App\Modules\Allocation\Infrastructure\*');

arch('lottery module does not import request persistence models (SC-005)')
    ->expect('App\Modules\Lottery')
    ->not->toUse('App\Modules\Request\Infrastructure\Persistence');

arch('lottery module does not import employee persistence models (SC-005)')
    ->expect('App\Modules\Lottery')
    ->not->toUse('App\Modules\Employee\Infrastructure\Persistence');

arch('lottery module does not import dormitory persistence models (SC-005)')
    ->expect('App\Modules\Lottery')
    ->not->toUse('App\Modules\Dormitory\Infrastructure\Persistence');

arch('lottery domain does not import foreign modules (SC-005)')
    ->expect('App\Modules\Lottery\Domain')
    ->not->toUse('App\Modules\Request\*')
    ->not->toUse('App\Modules\Employee\*')
    ->not->toUse('App\Modules\Dormitory\*')
    ->not->toUse('App\Modules\Allocation\*');

arch('lottery infrastructure does not import foreign domain layers (SC-005)')
    ->expect('App\Modules\Lottery\Infrastructure')
    ->not->toUse('App\Modules\Request\Domain\*')
    ->not->toUse('App\Modules\Employee\Domain\*')
    ->not->toUse('App\Modules\Dormitory\Domain\*')
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
    expect(app(LotteryResultReadContract::class))->toBeInstanceOf(LotteryResultReadService::class);
});
