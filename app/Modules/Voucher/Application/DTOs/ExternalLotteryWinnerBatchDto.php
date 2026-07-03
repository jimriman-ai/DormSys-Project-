<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\DTOs;

use App\Support\Exceptions\ValidationException;

/**
 * Stub batch shape — UD-03 remains open; required fields only.
 */
final readonly class ExternalLotteryWinnerBatchDto
{
    /**
     * @param  list<array<string, mixed>>  $winnerFacts
     */
    public function __construct(
        public string $programId,
        public bool $drawCompleted,
        public string $programType,
        public int $programCapacity,
        public array $winnerFacts,
    ) {
        if (trim($programId) === '') {
            throw new ValidationException('External lottery batch requires a non-empty program_id.');
        }

        if ($programCapacity < 0) {
            throw new ValidationException('External lottery program_capacity must be zero or positive.');
        }
    }

    public function isInternalProgram(): bool
    {
        return strtolower($this->programType) === 'internal';
    }

    /**
     * @param  array<string, mixed>  $facts
     */
    public static function fromUpstreamFacts(array $facts): self
    {
        $programId = self::requiredString($facts, 'program_id');
        $programType = self::requiredString($facts, 'program_type');
        $drawCompleted = filter_var($facts['draw_completed'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $programCapacity = (int) ($facts['program_capacity'] ?? 0);
        $winners = $facts['winners'] ?? [];

        if (! is_array($winners)) {
            throw new ValidationException('External lottery batch winners must be an array.');
        }

        /** @var list<array<string, mixed>> $winnerFacts */
        $winnerFacts = [];

        foreach ($winners as $index => $winner) {
            if (! is_array($winner)) {
                throw new ValidationException("External lottery winner at index {$index} must be an object.");
            }

            $winnerFacts[] = $winner;
        }

        return new self(
            programId: $programId,
            drawCompleted: $drawCompleted,
            programType: $programType,
            programCapacity: $programCapacity,
            winnerFacts: $winnerFacts,
        );
    }

    /**
     * @param  array<string, mixed>  $facts
     */
    private static function requiredString(array $facts, string $key): string
    {
        $value = $facts[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            throw new ValidationException("External lottery batch requires a non-empty {$key}.");
        }

        return trim($value);
    }
}
