<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects;

use App\Support\Exceptions\ValidationException;

/**
 * Sentinel identity for system-driven auto-approval decisions (OA-05-02 / DR-05-04).
 */
final readonly class SystemActorId
{
    public const string VALUE = '00000000-0000-7000-8000-000000000001';

    public function __construct(
        public string $value,
    ) {
        if ($value !== self::VALUE) {
            throw new ValidationException('Invalid system actor identifier.');
        }
    }

    public static function instance(): self
    {
        return new self(self::VALUE);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
