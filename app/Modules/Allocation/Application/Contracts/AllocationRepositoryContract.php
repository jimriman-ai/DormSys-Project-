<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Contracts;

use App\Modules\Allocation\Domain\Models\Allocation;
use App\Modules\Allocation\Domain\ValueObjects\AllocationId;
use App\Modules\Allocation\Domain\ValueObjects\PersonAllocationRef;

interface AllocationRepositoryContract
{
    public function save(Allocation $allocation): Allocation;

    public function findById(AllocationId $id): ?Allocation;

    /**
     * @return list<Allocation>
     */
    public function findActiveByPersonId(PersonAllocationRef $personId): array;
}
