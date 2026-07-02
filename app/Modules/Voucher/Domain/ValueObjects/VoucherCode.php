<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\ValueObjects;

use App\Support\Exceptions\ValidationException;

final readonly class VoucherCode
{
    public function __construct(
        public string $value,
    ) {
        if (! preg_match('/^[A-F0-9]{32}$/', $value)) {
            throw new ValidationException('Invalid voucher code format.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self(strtoupper($value));
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
