<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\DTOs;

use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Domain\ValueObjects\StayPeriod;
use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Stub intake shape — UD-03 remains open; required fields only.
 */
final readonly class InboundTriggerFactsDto
{
    /**
     * @param  array<string, mixed>  $upstreamFacts
     */
    public function __construct(
        public CorrelationId $correlationId,
        public string $employeeId,
        public TriggerSource $source,
        public StayPeriod $stayPeriod,
        public array $upstreamFacts,
        public ?string $dormitoryId = null,
        public ?string $requestId = null,
    ) {}

    /**
     * @param  array<string, mixed>  $facts
     */
    public static function fromLotteryFacts(array $facts): self
    {
        return self::fromUpstreamFacts($facts, TriggerSource::Lottery);
    }

    /**
     * @param  array<string, mixed>  $facts
     */
    public static function fromAllocationFacts(array $facts): self
    {
        return self::fromUpstreamFacts($facts, TriggerSource::Allocation);
    }

    /**
     * @param  array<string, mixed>  $facts
     */
    private static function fromUpstreamFacts(array $facts, TriggerSource $source): self
    {
        $correlationId = self::requiredString($facts, 'correlation_id');
        $employeeId = self::requiredString($facts, 'employee_id');
        $stayStart = self::requiredDate($facts, 'stay_start');
        $stayEnd = self::requiredDate($facts, 'stay_end');

        return new self(
            correlationId: CorrelationId::fromString($correlationId),
            employeeId: $employeeId,
            source: $source,
            stayPeriod: StayPeriod::fromDates($stayStart, $stayEnd),
            upstreamFacts: $facts,
            dormitoryId: self::optionalString($facts, 'dormitory_id'),
            requestId: self::optionalString($facts, 'request_id'),
        );
    }

    /**
     * @param  array<string, mixed>  $facts
     */
    private static function requiredString(array $facts, string $key): string
    {
        $value = $facts[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            throw new ValidationException("Trigger facts require a non-empty {$key}.");
        }

        return trim($value);
    }

    /**
     * @param  array<string, mixed>  $facts
     */
    private static function optionalString(array $facts, string $key): ?string
    {
        $value = $facts[$key] ?? null;

        if ($value === null) {
            return null;
        }

        if (! is_string($value) || trim($value) === '') {
            throw new ValidationException("Trigger facts {$key} must be a non-empty string when provided.");
        }

        return trim($value);
    }

    /**
     * @param  array<string, mixed>  $facts
     */
    private static function requiredDate(array $facts, string $key): DateTimeImmutable
    {
        $value = self::requiredString($facts, $key);

        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    }
}
