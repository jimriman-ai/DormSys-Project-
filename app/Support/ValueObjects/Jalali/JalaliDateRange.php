<?php

declare(strict_types=1);

namespace App\Support\ValueObjects\Jalali;

use App\Support\Exceptions\ValidationException;

/**
 * Immutable inclusive Jalali date range.
 */
final readonly class JalaliDateRange
{
    public function __construct(
        public JalaliDate $start,
        public JalaliDate $end,
    ) {
        if ($start->isAfter($end)) {
            throw new ValidationException('Jalali date range start must not be after end.');
        }
    }

    public static function fromStrings(string $start, string $end): self
    {
        return new self(
            start: JalaliDate::fromString($start),
            end: JalaliDate::fromString($end),
        );
    }

    public function contains(JalaliDate $date): bool
    {
        return ! $date->isBefore($this->start) && ! $date->isAfter($this->end);
    }

    public function equals(self $other): bool
    {
        return $this->start->equals($other->start) && $this->end->equals($other->end);
    }

    /**
     * @return array{start: array{year: int, month: int, day: int}, end: array{year: int, month: int, day: int}}
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->toArray(),
            'end' => $this->end->toArray(),
        ];
    }
}
