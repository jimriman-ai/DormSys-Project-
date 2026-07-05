<?php

declare(strict_types=1);

use App\Modules\Voucher\Application\Contracts\VoucherReadRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\Services\VoucherReadService;

arch('voucher module does not import allocation infrastructure')
    ->expect('App\Modules\Voucher')
    ->not->toUse('App\Modules\Allocation\Infrastructure\*');

arch('voucher module does not import lottery infrastructure')
    ->expect('App\Modules\Voucher')
    ->not->toUse('App\Modules\Lottery\Infrastructure\*');

arch('voucher module does not import request infrastructure')
    ->expect('App\Modules\Voucher')
    ->not->toUse('App\Modules\Request\Infrastructure\*');

arch('voucher module does not import allocation persistence models')
    ->expect('App\Modules\Voucher')
    ->not->toUse('App\Modules\Allocation\Infrastructure\Persistence');

arch('voucher module does not import lottery persistence models')
    ->expect('App\Modules\Voucher')
    ->not->toUse('App\Modules\Lottery\Infrastructure\Persistence');

arch('voucher module does not import request persistence models (SC-005)')
    ->expect('App\Modules\Voucher')
    ->not->toUse('App\Modules\Request\Infrastructure\Persistence');

arch('voucher domain does not import lottery modules (R8)')
    ->expect('App\Modules\Voucher\Domain')
    ->not->toUse('App\Modules\Lottery\*');

arch('voucher domain does not import allocation modules (R8)')
    ->expect('App\Modules\Voucher\Domain')
    ->not->toUse('App\Modules\Allocation\*');

arch('voucher domain does not import request modules (R8)')
    ->expect('App\Modules\Voucher\Domain')
    ->not->toUse('App\Modules\Request\*');

arch('voucher application does not import lottery command services (R8)')
    ->expect('App\Modules\Voucher\Application')
    ->not->toUse('App\Modules\Lottery\Application\*');

arch('voucher application does not import allocation command services (R8)')
    ->expect('App\Modules\Voucher\Application')
    ->not->toUse('App\Modules\Allocation\Application\*');

arch('voucher application does not import request command services (R8)')
    ->expect('App\Modules\Voucher\Application')
    ->not->toUse('App\Modules\Request\Application\*');

arch('voucher infrastructure does not import lottery domain layers (R8)')
    ->expect('App\Modules\Voucher\Infrastructure')
    ->not->toUse('App\Modules\Lottery\Domain\*');

arch('voucher infrastructure does not import allocation domain layers (R8)')
    ->expect('App\Modules\Voucher\Infrastructure')
    ->not->toUse('App\Modules\Allocation\Domain\*');

arch('voucher infrastructure does not import request domain layers (R8)')
    ->expect('App\Modules\Voucher\Infrastructure')
    ->not->toUse('App\Modules\Request\Domain\*');

test('voucher trigger intake contract is bound', function (): void {
    expect(app()->bound(VoucherTriggerIntakeContract::class))->toBeTrue();
    app(VoucherTriggerIntakeContract::class);
});

test('voucher read service depends only on voucher read repository (CD-017)', function (): void {
    $reflection = new ReflectionClass(VoucherReadService::class);
    $parameters = $reflection->getConstructor()?->getParameters() ?? [];

    expect($parameters)->toHaveCount(1);

    $parameterType = $parameters[0]->getType();
    if (! $parameterType instanceof ReflectionNamedType) {
        throw new UnexpectedValueException('Expected voucher read repository parameter type.');
    }

    expect($parameterType->getName())->toBe(VoucherReadRepositoryContract::class);
});
