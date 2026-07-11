<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Domain\ValueObjects;

use App\Modules\Dormitory\Domain\Exceptions\InvalidCapacity;

/**
 * Total and occupied physical capacity units.
 */
final readonly class Capacity
{
    public function __construct(
        public int $total,
        public int $occupied = 0,
    ) {
        if ($total < 0) {
            throw new InvalidCapacity('Capacity cannot be negative.');
        }

        if ($occupied < 0) {
            throw new InvalidCapacity('Occupied capacity cannot be negative.');
        }

        if ($occupied > $total) {
            throw new InvalidCapacity('Occupied capacity cannot exceed total physical capacity.');
        }
    }

    public static function of(int $total, int $occupied = 0): self
    {
        return new self($total, $occupied);
    }

    public function available(): int
    {
        return $this->total - $this->occupied;
    }

    public function withOccupied(int $occupied): self
    {
        return new self($this->total, $occupied);
    }

    public function equals(self $other): bool
    {
        return $this->total === $other->total && $this->occupied === $other->occupied;
    }
}
