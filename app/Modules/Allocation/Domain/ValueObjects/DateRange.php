<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Domain\ValueObjects;

use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;

final readonly class DateRange
{
    public function __construct(
        public DateTimeImmutable $start,
        public DateTimeImmutable $end,
    ) {
        if ($start > $end) {
            throw new ValidationException('Allocation date range start must not be after end.');
        }
    }

    public static function fromDates(DateTimeImmutable $start, DateTimeImmutable $end): self
    {
        return new self($start, $end);
    }

    public static function fromPostgresDaterange(string $value): self
    {
        if (! preg_match('/^[\[\(](\d{4}-\d{2}-\d{2}),(\d{4}-\d{2}-\d{2})[\]\)]$/', $value, $matches)) {
            throw new ValidationException('Invalid PostgreSQL daterange value.');
        }

        return new self(
            new DateTimeImmutable($matches[1]),
            new DateTimeImmutable($matches[2]),
        );
    }

    public function toPostgresDaterange(): string
    {
        return sprintf(
            '[%s,%s]',
            $this->start->format('Y-m-d'),
            $this->end->format('Y-m-d'),
        );
    }
}
