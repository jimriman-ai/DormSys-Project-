<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Contracts;

use App\Modules\Allocation\Domain\Models\Allocation;
use App\Modules\Allocation\Domain\ValueObjects\AllocationId;

interface AllocationRepositoryContract
{
    public function save(Allocation $allocation): Allocation;

    public function findById(AllocationId $id): ?Allocation;
}
