<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;

interface LotteryResultReadContract
{
    /**
     * @return array{
     *     program_id: string,
     *     winners: list<array{lottery_result_id: string, registration_id: string, rank: int}>,
     *     reserves: list<array{lottery_result_id: string, registration_id: string, rank: int}>,
     *     ranks: list<array{rank: int, lottery_result_id: string, registration_id: string, outcome: string}>
     * }
     *
     * `lottery_result_id` is `lottery_results.id` (A2 CLOSED Option A).
     */
    public function resultsForProgram(LotteryProgramId $programId): array;
}
