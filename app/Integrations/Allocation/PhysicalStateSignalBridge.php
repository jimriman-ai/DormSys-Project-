<?php

declare(strict_types=1);

namespace App\Integrations\Allocation;

use App\Modules\Allocation\Application\Contracts\Ports\PhysicalStateSignalPort;
use App\Modules\Dormitory\Application\Contracts\AllocationPhysicalStateApplicationContract;
use App\Modules\Dormitory\Application\DTOs\ApplyPhysicalStateSignalCommand;
use App\Modules\Dormitory\Application\Enums\PhysicalStateSignalType;
use App\Modules\Dormitory\Application\Exceptions\PhysicalStateSignalRejectedException;

/**
 * Allocation → Spec04 Dormitory physical-state signal live bridge.
 */
final class PhysicalStateSignalBridge implements PhysicalStateSignalPort
{
    public function __construct(
        private readonly AllocationPhysicalStateApplicationContract $signals,
    ) {}

    public function reserveBed(string $bedId, string $signalReferenceId): void
    {
        $this->applyOrFail($bedId, PhysicalStateSignalType::Reserve, $signalReferenceId);
    }

    public function occupyBed(string $bedId, string $signalReferenceId): void
    {
        $this->applyOrFail($bedId, PhysicalStateSignalType::OccupyMarker, $signalReferenceId);
    }

    public function releaseBed(string $bedId, string $signalReferenceId): void
    {
        $this->applyOrFail($bedId, PhysicalStateSignalType::Release, $signalReferenceId);
    }

    private function applyOrFail(
        string $bedId,
        PhysicalStateSignalType $type,
        string $signalReferenceId,
    ): void {
        $result = $this->signals->apply(new ApplyPhysicalStateSignalCommand(
            bedId: $bedId,
            signalType: $type,
            correlationId: $signalReferenceId,
        ));

        if (! $result->accepted) {
            throw new PhysicalStateSignalRejectedException(
                rejectionCode: $result->rejectionCode ?? 'rejected',
                message: sprintf(
                    'Physical state signal "%s" rejected for bed %s (%s).',
                    $type->value,
                    $bedId,
                    $result->rejectionCode ?? 'unknown',
                ),
            );
        }
    }
}
