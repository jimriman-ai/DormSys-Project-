<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Result of a resource status mutation.
 */
final readonly class ResourceStatusChangedData
{
    public function __construct(
        public string $id,
        public string $status,
    ) {}
}
