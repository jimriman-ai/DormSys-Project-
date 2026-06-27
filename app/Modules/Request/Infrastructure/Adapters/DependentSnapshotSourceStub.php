<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Adapters;

use App\Modules\Request\Application\Contracts\DependentSnapshotSourceContract;
use App\Modules\Request\Application\DTOs\DependentSnapshotReadDTO;

/**
 * In-memory fixture stub for Wave 1B (spec03 US3 not authorized).
 */
final class DependentSnapshotSourceStub implements DependentSnapshotSourceContract
{
    /**
     * @var array<string, DependentSnapshotReadDTO>
     */
    private array $fixtures = [];

    public function seed(DependentSnapshotReadDTO $snapshot): void
    {
        $this->fixtures[$this->key($snapshot->ownerEmployeeId, $snapshot->sourceDependentId)] = $snapshot;
    }

    public function mutateSnapshot(
        string $employeeId,
        string $sourceDependentId,
        string $firstName,
        string $lastName,
    ): void {
        $key = $this->key($employeeId, $sourceDependentId);
        $existing = $this->fixtures[$key] ?? null;

        if ($existing === null) {
            return;
        }

        $this->fixtures[$key] = new DependentSnapshotReadDTO(
            sourceDependentId: $existing->sourceDependentId,
            ownerEmployeeId: $existing->ownerEmployeeId,
            firstName: $firstName,
            lastName: $lastName,
            relationship: $existing->relationship,
            nationalCode: $existing->nationalCode,
            eligible: $existing->eligible,
        );
    }

    public function clear(): void
    {
        $this->fixtures = [];
    }

    public function findSnapshotForDependent(
        string $employeeId,
        string $sourceDependentId,
    ): ?DependentSnapshotReadDTO {
        return $this->fixtures[$this->key($employeeId, $sourceDependentId)] ?? null;
    }

    private function key(string $employeeId, string $sourceDependentId): string
    {
        return $employeeId.'|'.$sourceDependentId;
    }
}
