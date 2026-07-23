<?php

declare(strict_types=1);

use App\Integrations\Request\PendingRequestReadBridge;
use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;

arch('request module does not import employee domain enums (spec03 isolation)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Employee\Domain\Enums');

arch('request module does not import employee infrastructure (BT-R05)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Employee\Infrastructure\*')
    ->ignoring(architectureOptionCForeignPersistenceModelAllowlist());

arch('request module does not import employee domain entities (BT-R05)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Employee\Domain\Entities');

// Persistence-model import ban removed: contradicted DP-XMOD Option C allowlist.

arch('request module does not import dormitory infrastructure (BT-R05)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Dormitory\Infrastructure\*')
    ->ignoring(architectureOptionCForeignPersistenceModelAllowlist());

arch('request module does not import allocation infrastructure (BT-R05)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Allocation\Infrastructure\*');

arch('request module does not import lottery infrastructure (BT-R05)')
    ->expect('App\Modules\Request')
    ->not->toUse('App\Modules\Lottery\Infrastructure\*');

arch('request application does not import employee domain (BT-R05)')
    ->expect('App\Modules\Request\Application')
    ->not->toUse('App\Modules\Employee\Domain');

arch('request application does not import employee infrastructure (BT-R05)')
    ->expect('App\Modules\Request\Application')
    ->not->toUse('App\Modules\Employee\Infrastructure');

arch('request domain does not import employee modules (BT-R05)')
    ->expect('App\Modules\Request\Domain')
    ->not->toUse('App\Modules\Employee\*');

arch('request domain does not import dormitory modules (BT-R05)')
    ->expect('App\Modules\Request\Domain')
    ->not->toUse('App\Modules\Dormitory\*');

arch('request domain does not import allocation modules (BT-R05)')
    ->expect('App\Modules\Request\Domain')
    ->not->toUse('App\Modules\Allocation\*');

arch('request domain does not import lottery modules (BT-R05)')
    ->expect('App\Modules\Request\Domain')
    ->not->toUse('App\Modules\Lottery\*');

test('pending request read adapter implements only the employee read port (BT-R09 / OA-05-09)', function (): void {
    $adapterReflection = new ReflectionClass(PendingRequestReadBridge::class);
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
