<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Adapters;

use App\Modules\Lottery\Application\Contracts\ProposedAllocationPort;

final class NullProposedAllocationAdapter implements ProposedAllocationPort
{
    public function emitProposedAllocations(array $winners): void
    {
        // spec07 will consume proposed allocation payloads.
    }
}
