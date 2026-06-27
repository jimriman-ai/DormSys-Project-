<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

use App\Modules\Request\Domain\Entities\DependentSnapshot;
use App\Modules\Request\Domain\ValueObjects\RequestId;

interface DependentSnapshotRepositoryContract
{
    public function append(DependentSnapshot $snapshot): DependentSnapshot;

    /**
     * @return list<DependentSnapshot>
     */
    public function listForRequest(RequestId $requestId): array;
}
