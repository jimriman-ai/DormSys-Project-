<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\ValueObjects;

use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;
use DateTimeZone;

final readonly class StayPeriod
{
    public function __construct(
        public DateTimeImmutable $start,
        public DateTimeImmutable $end,
    ) {
        if ($start > $end) {
            throw new ValidationException('Stay period start must not be after end.');
        }
    }

    public static function fromDates(DateTimeImmutable $start, DateTimeImmutable $end): self
    {
        return new self($start, $end);
    }

    public static function fromPostgresDaterange(string $value): self
    {
        if (! preg_match('/^([\[\(])(\d{4}-\d{2}-\d{2}),(\d{4}-\d{2}-\d{2})([\]\)])$/', $value, $matches)) {
            throw new ValidationException('Invalid PostgreSQL daterange value.');
        }

        $utc = new DateTimeZone('UTC');
        $start = new DateTimeImmutable($matches[2], $utc);
        $end = new DateTimeImmutable($matches[3], $utc);

        if ($matches[1] === '(') {
            $start = $start->modify('+1 day');
        }

        if ($matches[4] === ')') {
            $end = $end->modify('-1 day');
        }

        return new self($start, $end);
    }

    public function toPostgresDaterange(): string
    {
        $upperExclusive = $this->end->modify('+1 day');

        return sprintf(
            '[%s,%s)',
            $this->start->format('Y-m-d'),
            $upperExclusive->format('Y-m-d'),
        );
    }

    public function overlaps(self $other): bool
    {
        return $this->start <= $other->end && $other->start <= $this->end;
    }
}
