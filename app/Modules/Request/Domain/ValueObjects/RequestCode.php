<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\ValueObjects;

use App\Support\Exceptions\ValidationException;

/**
 * Human-readable request code per spec05 R-02: REQ-{YYYYMMDD}-{seq}.
 */
final readonly class RequestCode
{
    private const string PATTERN = '/^REQ-(\d{8})-(\d{4})$/';

    public function __construct(
        public string $value,
    ) {
        if (! self::isValid($value)) {
            throw new ValidationException('Invalid request code format.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function isValid(string $value): bool
    {
        if (! preg_match(self::PATTERN, $value, $matches)) {
            return false;
        }

        $datePart = $matches[1];
        $sequence = (int) $matches[2];

        if ($sequence < 1 || $sequence > 9999) {
            return false;
        }

        $year = (int) substr($datePart, 0, 4);
        $month = (int) substr($datePart, 4, 2);
        $day = (int) substr($datePart, 6, 2);

        return checkdate($month, $day, $year);
    }

    public function datePart(): string
    {
        return $this->parsedParts()[0];
    }

    public function sequence(): int
    {
        return $this->parsedParts()[1];
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function parsedParts(): array
    {
        if (! preg_match(self::PATTERN, $this->value, $matches)) {
            throw new \LogicException('Request code invariant violated.');
        }

        return [$matches[1], (int) $matches[2]];
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
