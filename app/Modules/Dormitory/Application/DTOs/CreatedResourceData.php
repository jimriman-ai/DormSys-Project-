<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Created hierarchy resource identifier.
 */
final readonly class CreatedResourceData
{
    public function __construct(
        public string $id,
    ) {}
}
