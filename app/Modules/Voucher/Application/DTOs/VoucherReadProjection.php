<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\DTOs;

use App\Modules\Voucher\Domain\Models\Voucher;

final readonly class VoucherReadProjection
{
    public function __construct(
        public string $voucherId,
        public string $employeeId,
        public ?string $dormitoryId,
        public ?string $requestId,
        public string $code,
        public string $lifecycleState,
        public string $upstreamSource,
        public string $validityStart,
        public string $validityEnd,
        public string $stayStart,
        public string $stayEnd,
        public string $correlationId,
        public ?string $archivedAt,
    ) {}

    public static function fromDomain(Voucher $voucher): self
    {
        return new self(
            voucherId: $voucher->requireId()->value,
            employeeId: $voucher->employeeId,
            dormitoryId: $voucher->dormitoryId,
            requestId: $voucher->requestId,
            code: $voucher->code->value,
            lifecycleState: $voucher->lifecycleState->value,
            upstreamSource: $voucher->upstreamSource->value,
            validityStart: $voucher->validityStart->format('Y-m-d'),
            validityEnd: $voucher->validityEnd->format('Y-m-d'),
            stayStart: $voucher->stayPeriod->start->format('Y-m-d'),
            stayEnd: $voucher->stayPeriod->end->format('Y-m-d'),
            correlationId: $voucher->correlationId->value,
            archivedAt: $voucher->archivedAt?->format('Y-m-d\TH:i:s\Z'),
        );
    }
}
