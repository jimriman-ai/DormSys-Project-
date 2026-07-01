<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Domain\Models;

use App\Modules\Allocation\Domain\Enums\AllocationMethod;
use App\Modules\Allocation\Domain\Enums\AllocationStatus;
use App\Modules\Allocation\Domain\Exceptions\BedNotAssignableException;
use App\Modules\Allocation\Domain\Exceptions\InvalidAllocationTransitionException;
use App\Modules\Allocation\Domain\ValueObjects\AllocationId;
use App\Modules\Allocation\Domain\ValueObjects\DateRange;
use App\Modules\Allocation\Domain\ValueObjects\PersonAllocationRef;
use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class Allocation
{
    /**
     * @param  list<AllocationItem>  $items
     */
    public function __construct(
        public readonly ?AllocationId $id,
        public readonly PersonAllocationRef $personId,
        public readonly string $bedId,
        public readonly DateRange $dateRange,
        public readonly AllocationMethod $method,
        public AllocationStatus $status,
        public readonly ?string $sourceRequestId,
        public readonly ?string $sourceLotteryResultId,
        public ?DateTimeImmutable $releasedAt,
        public ?string $releaseReason,
        public array $items = [],
    ) {}

    public function requireId(): AllocationId
    {
        if ($this->id === null) {
            throw new \LogicException('Allocation identifier is not assigned.');
        }

        return $this->id;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    /**
     * @param  list<AllocationItem>  $items
     */
    public static function assign(
        PersonAllocationRef $personId,
        string $bedId,
        DateRange $dateRange,
        AllocationMethod $method,
        ?string $sourceRequestId = null,
        ?string $sourceLotteryResultId = null,
        array $items = [],
    ): self {
        self::assertAssignableBedRef($bedId);
        self::assertMethodSources($method, $sourceRequestId, $sourceLotteryResultId);

        if ($items === []) {
            $items = [AllocationItem::forBed($bedId)];
        }

        return new self(
            id: null,
            personId: $personId,
            bedId: $bedId,
            dateRange: $dateRange,
            method: $method,
            status: AllocationStatus::Active,
            sourceRequestId: $sourceRequestId,
            sourceLotteryResultId: $sourceLotteryResultId,
            releasedAt: null,
            releaseReason: null,
            items: $items,
        );
    }

    public function release(string $reason, DateTimeImmutable $releasedAt): self
    {
        if (! $this->isActive()) {
            throw new InvalidAllocationTransitionException('Only active allocations can be released.');
        }

        $trimmedReason = trim($reason);

        if ($trimmedReason === '') {
            throw new ValidationException('Release reason is required.');
        }

        return new self(
            id: $this->id,
            personId: $this->personId,
            bedId: $this->bedId,
            dateRange: $this->dateRange,
            method: $this->method,
            status: AllocationStatus::Released,
            sourceRequestId: $this->sourceRequestId,
            sourceLotteryResultId: $this->sourceLotteryResultId,
            releasedAt: $releasedAt,
            releaseReason: $trimmedReason,
            items: $this->items,
        );
    }

    private static function assertAssignableBedRef(string $bedId): void
    {
        if (! Uuid::isValid($bedId)) {
            throw new BedNotAssignableException('Invalid bed reference.');
        }
    }

    private static function assertMethodSources(
        AllocationMethod $method,
        ?string $sourceRequestId,
        ?string $sourceLotteryResultId,
    ): void {
        if ($method === AllocationMethod::RequestSourced && ($sourceRequestId === null || ! Uuid::isValid($sourceRequestId))) {
            throw new ValidationException('Request-sourced allocations require a valid source request reference.');
        }

        if ($method === AllocationMethod::LotterySourced && ($sourceLotteryResultId === null || ! Uuid::isValid($sourceLotteryResultId))) {
            throw new ValidationException('Lottery-sourced allocations require a valid source lottery result reference.');
        }
    }
}
