<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Uuid;

use Ramsey\Uuid\Uuid;

final class UuidGenerator
{
    public static function uuid7(): string
    {
        return Uuid::uuid7()->toString();
    }
}
