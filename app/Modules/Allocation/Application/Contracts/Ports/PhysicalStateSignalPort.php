<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Contracts\Ports;

/**
 * Allocation producer port for spec04 AllocationPhysicalStatePort (UD-07 stub until live consumer).
 */
interface PhysicalStateSignalPort
{
    public function reserveBed(string $bedId, string $signalReferenceId): void;

    public function occupyBed(string $bedId, string $signalReferenceId): void;

    public function releaseBed(string $bedId, string $signalReferenceId): void;
}
