<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Domain\Models;

use App\Modules\Allocation\Domain\ValueObjects\AllocationId;
use App\Modules\Allocation\Domain\ValueObjects\AllocationItemId;

final class AllocationItem
{
    public function __construct(
        public readonly ?AllocationItemId $id,
        public readonly ?AllocationId $allocationId,
        public readonly string $bedId,
        public readonly int $sequence,
    ) {}

    public static function forBed(string $bedId, int $sequence = 1): self
    {
        return new self(
            id: null,
            allocationId: null,
            bedId: $bedId,
            sequence: $sequence,
        );
    }

    public function requireId(): AllocationItemId
    {
        if ($this->id === null) {
            throw new \LogicException('Allocation item identifier is not assigned.');
        }

        return $this->id;
    }
}
