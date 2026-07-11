<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Adapters;

use App\Modules\Allocation\Application\Contracts\Ports\PhysicalStateSignalPort;

final class AllocationPhysicalStateAdapter
{
    public function __construct(
        private readonly PhysicalStateSignalPort $physicalState,
    ) {}

    /**
     * @param  bool  $requireOccupiedMarker  When true, follow reserve with occupyBed (ADIC optional path).
     */
    public function signalAssigned(
        string $bedId,
        string $signalReferenceId,
        bool $requireOccupiedMarker = false,
    ): void {
        $this->physicalState->reserveBed($bedId, $signalReferenceId);

        if ($requireOccupiedMarker) {
            $this->physicalState->occupyBed($bedId, $signalReferenceId);
        }
    }

    public function signalReleased(string $bedId, string $signalReferenceId): void
    {
        $this->physicalState->releaseBed($bedId, $signalReferenceId);
    }
}
