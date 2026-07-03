<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Models;

use App\Modules\Voucher\Domain\Enums\TriggerIntakeStatus;
use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Domain\ValueObjects\StayPeriod;
use App\Modules\Voucher\Domain\ValueObjects\TriggerId;
use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class VoucherIssuanceTrigger
{
    /**
     * @param  array<string, mixed>  $upstreamFacts
     */
    public function __construct(
        public readonly ?TriggerId $id,
        public readonly CorrelationId $correlationId,
        public readonly string $employeeId,
        public readonly TriggerSource $source,
        public readonly StayPeriod $stayPeriod,
        public TriggerIntakeStatus $status,
        public readonly ?string $dormitoryId,
        public readonly ?string $requestId,
        public readonly array $upstreamFacts,
        public ?DateTimeImmutable $issuancePathCompletedAt,
        public ?TriggerId $supersededByTriggerId,
    ) {}

    /**
     * @param  array<string, mixed>  $upstreamFacts
     */
    public static function accept(
        CorrelationId $correlationId,
        string $employeeId,
        TriggerSource $source,
        StayPeriod $stayPeriod,
        array $upstreamFacts,
        ?string $dormitoryId = null,
        ?string $requestId = null,
    ): self {
        self::assertEmployeeRef($employeeId);
        self::assertOptionalUuidRef($dormitoryId, 'dormitory');
        self::assertOptionalUuidRef($requestId, 'request');

        return new self(
            id: null,
            correlationId: $correlationId,
            employeeId: $employeeId,
            source: $source,
            stayPeriod: $stayPeriod,
            status: TriggerIntakeStatus::Accepted,
            dormitoryId: $dormitoryId,
            requestId: $requestId,
            upstreamFacts: $upstreamFacts,
            issuancePathCompletedAt: null,
            supersededByTriggerId: null,
        );
    }

    public function requireId(): TriggerId
    {
        if ($this->id === null) {
            throw new \LogicException('Trigger identifier is not assigned.');
        }

        return $this->id;
    }

    public function isActiveCommitment(): bool
    {
        return $this->status === TriggerIntakeStatus::Accepted
            && $this->issuancePathCompletedAt === null;
    }

    public function hasCompletedIssuancePath(): bool
    {
        return $this->issuancePathCompletedAt !== null;
    }

    public function supersede(TriggerId $supersedingTriggerId): self
    {
        if ($this->status !== TriggerIntakeStatus::Accepted) {
            throw new ValidationException('Only accepted triggers can be superseded.');
        }

        return new self(
            id: $this->id,
            correlationId: $this->correlationId,
            employeeId: $this->employeeId,
            source: $this->source,
            stayPeriod: $this->stayPeriod,
            status: TriggerIntakeStatus::Superseded,
            dormitoryId: $this->dormitoryId,
            requestId: $this->requestId,
            upstreamFacts: $this->upstreamFacts,
            issuancePathCompletedAt: $this->issuancePathCompletedAt,
            supersededByTriggerId: $supersedingTriggerId,
        );
    }

    public function markIssuancePathCompleted(DateTimeImmutable $completedAt): self
    {
        return new self(
            id: $this->id,
            correlationId: $this->correlationId,
            employeeId: $this->employeeId,
            source: $this->source,
            stayPeriod: $this->stayPeriod,
            status: $this->status,
            dormitoryId: $this->dormitoryId,
            requestId: $this->requestId,
            upstreamFacts: $this->upstreamFacts,
            issuancePathCompletedAt: $completedAt,
            supersededByTriggerId: $this->supersededByTriggerId,
        );
    }

    private static function assertEmployeeRef(string $employeeId): void
    {
        if (! Uuid::isValid($employeeId)) {
            throw new ValidationException('Invalid employee reference.');
        }
    }

    private static function assertOptionalUuidRef(?string $value, string $label): void
    {
        if ($value !== null && ! Uuid::isValid($value)) {
            throw new ValidationException("Invalid {$label} reference.");
        }
    }
}
