<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

interface ProposedAllocationPort
{
    /**
     * @param  list<array{
     *     program_id: string,
     *     registration_id: string,
     *     employee_id: string,
     *     dormitory_id: string,
     *     rank: int
     * }>  $winners
     */
    public function emitProposedAllocations(array $winners): void;
}
