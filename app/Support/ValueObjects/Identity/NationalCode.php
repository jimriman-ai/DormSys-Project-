<?php

declare(strict_types=1);

namespace App\Support\ValueObjects\Identity;

use App\Support\Exceptions\ValidationException;

/**
 * Immutable Iranian national identification code (کد ملی).
 */
final readonly class NationalCode
{
    public function __construct(
        public string $value,
    ) {
        if ($value !== self::normalize($value) || ! self::isValid($value)) {
            throw new ValidationException('Invalid Iranian national code.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self(self::normalize($value));
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function isValid(string $value): bool
    {
        $code = self::normalize($value);

        if (! preg_match('/^\d{10}$/', $code)) {
            return false;
        }

        if (preg_match('/^(\d)\1{9}$/', $code)) {
            return false;
        }

        $checkDigit = (int) $code[9];
        $sum = 0;

        for ($index = 0; $index < 9; $index++) {
            $sum += (int) $code[$index] * (10 - $index);
        }

        $remainder = $sum % 11;

        return ($remainder < 2 && $checkDigit === $remainder)
            || ($remainder >= 2 && $checkDigit === (11 - $remainder));
    }

    private static function normalize(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }
}
