<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Adapters;

use App\Modules\Allocation\Application\Contracts\Ports\PhysicalStateSignalPort;

final class NullPhysicalStateSignalAdapter implements PhysicalStateSignalPort
{
    public function reserveBed(string $bedId, string $signalReferenceId): void {}

    public function occupyBed(string $bedId, string $signalReferenceId): void {}

    public function releaseBed(string $bedId, string $signalReferenceId): void {}
}
