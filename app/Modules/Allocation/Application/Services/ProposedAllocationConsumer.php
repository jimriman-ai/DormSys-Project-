<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Services;

use App\Modules\Allocation\Domain\Enums\AllocationMethod;
use App\Modules\Lottery\Application\Contracts\ProposedAllocationPort;
use DateTimeImmutable;
use DateTimeZone;

final class ProposedAllocationConsumer implements ProposedAllocationPort
{
    public function __construct(
        private readonly CreateAllocationAction $createAllocation,
    ) {}

    /**
     * @param  list<array{
     *     program_id: string,
     *     registration_id: string,
     *     employee_id: string,
     *     dormitory_id: string,
     *     rank: int
     * }>  $winners
     */
    public function emitProposedAllocations(array $winners): void
    {
        foreach ($winners as $winner) {
            $this->createAllocation->execute(
                personId: $winner['employee_id'],
                bedId: $winner['dormitory_id'],
                start: new DateTimeImmutable('now', new DateTimeZone('UTC')),
                end: new DateTimeImmutable('+1 year', new DateTimeZone('UTC')),
                method: AllocationMethod::LotterySourced,
                sourceLotteryResultId: $winner['registration_id'],
            );
        }
    }
}
