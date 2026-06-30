<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;

interface LotteryResultReadContract
{
    /**
     * @return list<array{
     *     registration_id: string,
     *     program_id: string,
     *     rank: int,
     *     outcome: string
     * }>
     */
    public function resultsForProgram(LotteryProgramId $programId): array;
}
