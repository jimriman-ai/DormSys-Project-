<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Domain\ValueObjects;

use App\Modules\Dormitory\Domain\Exceptions\InvalidCapacity;

/**
 * Current usable availability projection for a resource.
 */
final readonly class Availability
{
    public function __construct(
        public int $availableCount,
        public bool $isAvailable,
    ) {
        if ($availableCount < 0) {
            throw new InvalidCapacity('Available capacity cannot be negative.');
        }

        if ($isAvailable && $availableCount === 0) {
            throw new InvalidCapacity('Availability cannot be true when available count is zero.');
        }

        if (! $isAvailable && $availableCount > 0) {
            throw new InvalidCapacity('Availability cannot be false when available count is positive.');
        }
    }

    public static function none(): self
    {
        return new self(0, false);
    }

    public static function of(int $availableCount): self
    {
        return new self($availableCount, $availableCount > 0);
    }

    public function equals(self $other): bool
    {
        return $this->availableCount === $other->availableCount
            && $this->isAvailable === $other->isAvailable;
    }
}
