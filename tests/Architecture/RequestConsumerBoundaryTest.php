<?php

declare(strict_types=1);

use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;
use App\Modules\Request\Infrastructure\Adapters\PendingRequestReadAdapter;

arch('request module does not import employee domain enums (spec03 isolation)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Employee\Domain\Enums');

arch('request module does not import employee infrastructure (BT-R05)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Employee\Infrastructure\*');

arch('request module does not import employee domain entities (BT-R05)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Employee\Domain\Entities');

arch('request module does not import employee persistence models (BT-R05)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Employee\Infrastructure\Persistence');

arch('request module does not import dormitory infrastructure (BT-R05)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Dormitory\Infrastructure\*');

arch('request module does not import allocation infrastructure (BT-R05)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Allocation\Infrastructure\*');

arch('request module does not import lottery infrastructure (BT-R05)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Lottery\Infrastructure\*');

arch('request application imports employee only through application contracts (BT-R05)')
    ->expect('App\Modules\Request\Application')
    ->not->toUse('App\Modules\Employee\Domain')
    ->not->toUse('App\Modules\Employee\Infrastructure');

arch('request domain does not import foreign modules (BT-R05)')
    ->expect('App\Modules\Request\Domain')
    ->not->toUse('App\Modules\Employee\*')
    ->not->toUse('App\Modules\Dormitory\*')
    ->not->toUse('App\Modules\Allocation\*')
    ->not->toUse('App\Modules\Lottery\*');

test('pending request read adapter implements only the employee read port (BT-R09 / OA-05-09)', function (): void {
    $adapterReflection = new ReflectionClass(PendingRequestReadAdapter::class);
    $portReflection = new ReflectionClass(PendingRequestReadPort::class);

    expect($adapterReflection->implementsInterface(PendingRequestReadPort::class))->toBeTrue();

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
