<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\DTOs;

/**
 * Immutable dependent read projection from the snapshot source port (CD-009).
 * No spec03 types — Request-owned DTO boundary.
 */
final readonly class DependentSnapshotReadDTO
{
    public function __construct(
        public string $sourceDependentId,
        public string $ownerEmployeeId,
        public string $firstName,
        public string $lastName,
        public string $relationship,
        public ?string $nationalCode,
        public bool $eligible,
    ) {}
}
