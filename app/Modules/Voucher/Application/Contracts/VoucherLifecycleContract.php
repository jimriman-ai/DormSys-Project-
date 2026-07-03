<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;
use DateTimeImmutable;

interface VoucherLifecycleContract
{
    public function expire(VoucherId $voucherId, DateTimeImmutable $asOf): Voucher;

    public function archive(VoucherId $voucherId, DateTimeImmutable $archivedAt): Voucher;

    public function cancel(VoucherId $voucherId, DateTimeImmutable $occurredAt): Voucher;

    public function supersede(VoucherId $voucherId, DateTimeImmutable $occurredAt): Voucher;
}
