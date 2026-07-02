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

arch('voucher domain does not import foreign modules (R8)')
    ->expect('App\Modules\Voucher\Domain')
    ->not->toUse('App\Modules\Lottery\*')
    ->not->toUse('App\Modules\Allocation\*')
    ->not->toUse('App\Modules\Request\*');

arch('voucher application does not import upstream command services (R8)')
    ->expect('App\Modules\Voucher\Application')
    ->not->toUse('App\Modules\Lottery\Application\*')
    ->not->toUse('App\Modules\Allocation\Application\*')
    ->not->toUse('App\Modules\Request\Application\*');

arch('voucher infrastructure does not import foreign domain layers (R8)')
    ->expect('App\Modules\Voucher\Infrastructure')
    ->not->toUse('App\Modules\Lottery\Domain\*')
    ->not->toUse('App\Modules\Allocation\Domain\*')
    ->not->toUse('App\Modules\Request\Domain\*');

test('voucher trigger intake contract is bound', function (): void {
    expect(app(VoucherTriggerIntakeContract::class))->toBeInstanceOf(VoucherTriggerIntakeContract::class);
});

test('voucher read service depends only on voucher read repository (CD-017)', function (): void {
    $reflection = new ReflectionClass(VoucherReadService::class);
    $parameters = $reflection->getConstructor()?->getParameters() ?? [];

    expect($parameters)->toHaveCount(1);
    expect($parameters[0]->getType()?->getName())
        ->toBe(VoucherReadRepositoryContract::class);
});
