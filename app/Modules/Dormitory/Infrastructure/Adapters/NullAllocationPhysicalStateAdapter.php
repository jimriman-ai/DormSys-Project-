<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Adapters;

use App\Modules\Dormitory\Application\Contracts\Ports\AllocationPhysicalStatePort;
use App\Modules\Dormitory\Domain\ValueObjects\BedId;

final class NullAllocationPhysicalStateAdapter implements AllocationPhysicalStatePort
{
    public function reserveBed(BedId $bedId, string $signalReferenceId): void {}

    public function occupyBed(BedId $bedId, string $signalReferenceId): void {}

    public function releaseBed(BedId $bedId, string $signalReferenceId): void {}
}
