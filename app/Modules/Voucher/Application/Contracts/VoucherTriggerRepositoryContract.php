<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Domain\Models\VoucherIssuanceTrigger;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Domain\ValueObjects\StayPeriod;
use App\Modules\Voucher\Domain\ValueObjects\TriggerId;

interface VoucherTriggerRepositoryContract
{
    public function save(VoucherIssuanceTrigger $trigger): VoucherIssuanceTrigger;

    public function findByCorrelationId(CorrelationId $correlationId): ?VoucherIssuanceTrigger;

    /**
     * @return list<VoucherIssuanceTrigger>
     */
    public function findActiveOverlappingForEmployee(string $employeeId, StayPeriod $stayPeriod): array;

    public function markIssuancePathCompleted(TriggerId $triggerId): VoucherIssuanceTrigger;
}
