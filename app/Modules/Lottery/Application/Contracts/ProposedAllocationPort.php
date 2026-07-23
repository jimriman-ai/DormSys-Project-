<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

interface ProposedAllocationPort
{
    /**
     * @param  list<array{
     *     program_id: string,
     *     lottery_result_id: string,
     *     registration_id: string,
     *     employee_id: string,
     *     dormitory_id: string,
     *     rank: int
     * }>  $winners
     *
     * `lottery_result_id` must be `lottery_results.id` (A2 CLOSED Option A).
     */
    public function emitProposedAllocations(array $winners): void;
}
