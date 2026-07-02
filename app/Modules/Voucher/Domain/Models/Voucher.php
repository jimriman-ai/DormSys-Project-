<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Models;

use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Domain\Exceptions\InvalidVoucherTransitionException;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Domain\ValueObjects\EligibilityOutcomeId;
use App\Modules\Voucher\Domain\ValueObjects\StayPeriod;
use App\Modules\Voucher\Domain\ValueObjects\TriggerId;
use App\Modules\Voucher\Domain\ValueObjects\VoucherCode;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;
use DateTimeImmutable;

final class Voucher
{
    public function __construct(
        public readonly ?VoucherId $id,
        public readonly EligibilityOutcomeId $eligibilityOutcomeId,
        public readonly TriggerId $triggerId,
        public readonly CorrelationId $correlationId,
        public readonly string $employeeId,
        public readonly ?string $dormitoryId,
        public readonly ?string $requestId,
        public readonly TriggerSource $upstreamSource,
        public readonly VoucherCode $code,
        public VoucherLifecycleState $lifecycleState,
        public readonly StayPeriod $stayPeriod,
        public readonly DateTimeImmutable $validityStart,
        public readonly DateTimeImmutable $validityEnd,
        public readonly DateTimeImmutable $issuedAt,
        public ?DateTimeImmutable $archivedAt,
    ) {}

    public static function issue(
        EligibilityOutcomeId $eligibilityOutcomeId,
        TriggerId $triggerId,
        CorrelationId $correlationId,
        string $employeeId,
        ?string $dormitoryId,
        ?string $requestId,
        TriggerSource $upstreamSource,
        VoucherCode $code,
        StayPeriod $stayPeriod,
        DateTimeImmutable $issuedAt,
    ): self {
        return new self(
            id: null,
            eligibilityOutcomeId: $eligibilityOutcomeId,
            triggerId: $triggerId,
            correlationId: $correlationId,
            employeeId: $employeeId,
            dormitoryId: $dormitoryId,
            requestId: $requestId,
            upstreamSource: $upstreamSource,
            code: $code,
            lifecycleState: VoucherLifecycleState::Issued,
            stayPeriod: $stayPeriod,
            validityStart: $stayPeriod->start,
            validityEnd: $stayPeriod->end,
            issuedAt: $issuedAt,
            archivedAt: null,
        );
    }

    public function requireId(): VoucherId
    {
        if ($this->id === null) {
            throw new \LogicException('Voucher identifier is not assigned.');
        }

        return $this->id;
    }

    public function isTerminal(): bool
    {
        return $this->lifecycleState->isTerminal();
    }

    public function expire(DateTimeImmutable $expiredAt): self
    {
        if ($this->lifecycleState !== VoucherLifecycleState::Issued) {
            throw new InvalidVoucherTransitionException('Only issued vouchers can expire.');
        }

        if ($expiredAt->format('Y-m-d') <= $this->validityEnd->format('Y-m-d')) {
            throw new InvalidVoucherTransitionException('Voucher validity window has not ended.');
        }

        return $this->withState(VoucherLifecycleState::Expired);
    }

    public function cancel(): self
    {
        if ($this->lifecycleState !== VoucherLifecycleState::Issued) {
            throw new InvalidVoucherTransitionException('Only issued vouchers can be cancelled.');
        }

        return $this->withState(VoucherLifecycleState::Cancelled);
    }

    public function supersede(): self
    {
        if ($this->lifecycleState !== VoucherLifecycleState::Issued) {
            throw new InvalidVoucherTransitionException('Only issued vouchers can be superseded.');
        }

        return $this->withState(VoucherLifecycleState::Superseded);
    }

    public function archive(DateTimeImmutable $archivedAt): self
    {
        if ($this->archivedAt !== null) {
            throw new InvalidVoucherTransitionException('Voucher is already archived.');
        }

        return new self(
            id: $this->id,
            eligibilityOutcomeId: $this->eligibilityOutcomeId,
            triggerId: $this->triggerId,
            correlationId: $this->correlationId,
            employeeId: $this->employeeId,
            dormitoryId: $this->dormitoryId,
            requestId: $this->requestId,
            upstreamSource: $this->upstreamSource,
            code: $this->code,
            lifecycleState: $this->lifecycleState,
            stayPeriod: $this->stayPeriod,
            validityStart: $this->validityStart,
            validityEnd: $this->validityEnd,
            issuedAt: $this->issuedAt,
            archivedAt: $archivedAt,
        );
    }

    private function withState(VoucherLifecycleState $state): self
    {
        return new self(
            id: $this->id,
            eligibilityOutcomeId: $this->eligibilityOutcomeId,
            triggerId: $this->triggerId,
            correlationId: $this->correlationId,
            employeeId: $this->employeeId,
            dormitoryId: $this->dormitoryId,
            requestId: $this->requestId,
            upstreamSource: $this->upstreamSource,
            code: $this->code,
            lifecycleState: $state,
            stayPeriod: $this->stayPeriod,
            validityStart: $this->validityStart,
            validityEnd: $this->validityEnd,
            issuedAt: $this->issuedAt,
            archivedAt: $this->archivedAt,
        );
    }
}
