<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Models;

use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;
use DateTimeImmutable;

final class VoucherLifecycleTransition
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly ?string $id,
        public readonly VoucherId $voucherId,
        public readonly ?VoucherLifecycleState $fromState,
        public readonly VoucherLifecycleState $toState,
        public readonly CorrelationId $correlationId,
        public readonly DateTimeImmutable $occurredAt,
        public readonly array $payload,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function record(
        VoucherId $voucherId,
        ?VoucherLifecycleState $fromState,
        VoucherLifecycleState $toState,
        CorrelationId $correlationId,
        DateTimeImmutable $occurredAt,
        array $payload,
    ): self {
        return new self(
            id: null,
            voucherId: $voucherId,
            fromState: $fromState,
            toState: $toState,
            correlationId: $correlationId,
            occurredAt: $occurredAt,
            payload: $payload,
        );
    }
}
