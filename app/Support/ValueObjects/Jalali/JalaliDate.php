<?php

declare(strict_types=1);

namespace App\Support\ValueObjects\Jalali;

use App\Support\Exceptions\ValidationException;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Morilog\Jalali\Jalalian;

/**
 * Immutable Jalali calendar date (year, month, day) without time component.
 */
final readonly class JalaliDate
{
    public function __construct(
        public int $year,
        public int $month,
        public int $day,
    ) {
        if ($year < 1 || $month < 1 || $month > 12 || $day < 1 || $day > 31) {
            throw new ValidationException('Invalid Jalali date components.');
        }

        try {
            Jalalian::fromFormat('Y/m/d', sprintf('%04d/%02d/%02d', $year, $month, $day));
        } catch (\Throwable) {
            throw new ValidationException('Invalid Jalali date.');
        }
    }

    public static function fromDateTime(DateTimeInterface|string $dateTime): self
    {
        $jalali = Jalalian::fromDateTime($dateTime);

        return new self(
            year: (int) $jalali->getYear(),
            month: (int) $jalali->getMonth(),
            day: (int) $jalali->getDay(),
        );
    }

    public static function fromString(string $value): self
    {
        $normalized = str_replace('-', '/', trim($value));

        if (! preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $normalized)) {
            throw new ValidationException('Jalali date must be in Y/m/d format.');
        }

        [$year, $month, $day] = array_map('intval', explode('/', $normalized));

        return new self($year, $month, $day);
    }

    public function toGregorian(): CarbonInterface
    {
        return Jalalian::fromFormat('Y/m/d', $this->toString())->toCarbon();
    }

    public function toString(): string
    {
        return sprintf('%04d/%02d/%02d', $this->year, $this->month, $this->day);
    }

    public function equals(self $other): bool
    {
        return $this->year === $other->year
            && $this->month === $other->month
            && $this->day === $other->day;
    }

    public function isBefore(self $other): bool
    {
        return $this->ordinal() < $other->ordinal();
    }

    public function isAfter(self $other): bool
    {
        return $this->ordinal() > $other->ordinal();
    }

    /**
     * @return array{year: int, month: int, day: int}
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
        ];
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function ordinal(): int
    {
        return ($this->year * 10_000) + ($this->month * 100) + $this->day;
    }
}
