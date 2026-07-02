<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\DTOs;

use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Support\Exceptions\ValidationException;
use Ramsey\Uuid\Uuid;

/**
 * Stub intake shape — UD-03 remains open; required fields only.
 */
final readonly class ReservePromotionTriggerFactsDto
{
    /**
     * @param  array<string, mixed>  $upstreamFacts
     * @param  array<string, mixed>|null  $reserveFacts
     */
    public function __construct(
        public CorrelationId $correlationId,
        public string $programId,
        public string $programType,
        public string $priorWinnerVoucherId,
        public string $promotionReason,
        public ?array $reserveFacts,
        public array $upstreamFacts,
    ) {}

    public function isInternalProgram(): bool
    {
        return strtolower($this->programType) === 'internal';
    }

    public function hasReserveCandidate(): bool
    {
        return $this->reserveFacts !== null && $this->reserveFacts !== [];
    }

    /**
     * @param  array<string, mixed>  $facts
     */
    public static function fromUpstreamFacts(array $facts): self
    {
        $correlationId = self::requiredString($facts, 'correlation_id');
        $programId = self::requiredString($facts, 'program_id');
        $programType = self::requiredString($facts, 'program_type');
        $priorWinnerVoucherId = self::requiredString($facts, 'prior_winner_voucher_id');
        $promotionReason = self::requiredString($facts, 'promotion_reason');

        if (! Uuid::isValid($priorWinnerVoucherId)) {
            throw new ValidationException('Reserve promotion requires a valid prior_winner_voucher_id.');
        }

        $reserve = $facts['reserve'] ?? null;

        if ($reserve !== null && ! is_array($reserve)) {
            throw new ValidationException('Reserve promotion reserve facts must be an object when provided.');
        }

        /** @var array<string, mixed>|null $reserveFacts */
        $reserveFacts = is_array($reserve) && $reserve !== [] ? $reserve : null;

        return new self(
            correlationId: CorrelationId::fromString($correlationId),
            programId: $programId,
            programType: $programType,
            priorWinnerVoucherId: $priorWinnerVoucherId,
            promotionReason: $promotionReason,
            reserveFacts: $reserveFacts,
            upstreamFacts: $facts,
        );
    }

    /**
     * @param  array<string, mixed>  $facts
     */
    private static function requiredString(array $facts, string $key): string
    {
        $value = $facts[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            throw new ValidationException("Reserve promotion facts require a non-empty {$key}.");
        }

        return trim($value);
    }
}
