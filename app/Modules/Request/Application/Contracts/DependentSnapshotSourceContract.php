<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

use App\Modules\Request\Application\DTOs\DependentSnapshotReadDTO;

/**
 * Read-only port for FamilyDirect dependent snapshots (CD-009).
 * Wave 1B: backed by DependentSnapshotSourceStub until spec03 US3 is authorized.
 */
interface DependentSnapshotSourceContract
{
    public function findSnapshotForDependent(
        string $employeeId,
        string $sourceDependentId,
    ): ?DependentSnapshotReadDTO;
}
