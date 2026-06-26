<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\DTOs;

/**
 * Read-only cross-context projection (FR-008).
 */
final readonly class UserSummaryDTO
{
    public function __construct(
        public string $id,
        public string $status,
        public string $displayName,
    ) {}
}
