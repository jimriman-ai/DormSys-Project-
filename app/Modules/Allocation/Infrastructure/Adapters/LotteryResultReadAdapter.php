<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Adapters;

use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;

final class LotteryResultReadAdapter
{
    public function __construct(
        private readonly LotteryResultReadContract $lotteryResults,
    ) {}

    /**
     * @return array{
     *     program_id: string,
     *     winners: list<array{registration_id: string, rank: int}>,
     *     reserves: list<array{registration_id: string, rank: int}>,
     *     ranks: list<array{rank: int, registration_id: string, outcome: string}>
     * }
     */
    public function resultsForProgram(LotteryProgramId $programId): array
    {
        return $this->lotteryResults->resultsForProgram($programId);
    }
}
