<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;

interface LotteryResultReadContract
{
    /**
     * @return array{
     *     program_id: string,
     *     winners: list<array{registration_id: string, rank: int}>,
     *     reserves: list<array{registration_id: string, rank: int}>,
     *     ranks: list<array{rank: int, registration_id: string, outcome: string}>
     * }
     */
    public function resultsForProgram(LotteryProgramId $programId): array;
}
