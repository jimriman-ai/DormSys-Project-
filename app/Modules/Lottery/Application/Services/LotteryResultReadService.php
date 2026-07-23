<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Application\Contracts\LotteryResultRepositoryContract;
use App\Modules\Lottery\Domain\Enums\LotteryResultOutcome;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;

final class LotteryResultReadService implements LotteryResultReadContract
{
    public function __construct(
        private readonly LotteryResultRepositoryContract $results,
    ) {}

    public function resultsForProgram(LotteryProgramId $programId): array
    {
        $winners = [];
        $reserves = [];
        $ranks = [];

        foreach ($this->results->findByProgramId($programId) as $result) {
            $winnerOrReserveRow = [
                'lottery_result_id' => $result->requireId()->value,
                'registration_id' => $result->registrationId->value,
                'rank' => $result->rank,
            ];

            $ranks[] = [
                'rank' => $result->rank,
                'lottery_result_id' => $result->requireId()->value,
                'registration_id' => $result->registrationId->value,
                'outcome' => $result->outcome->value,
            ];

            if ($result->outcome === LotteryResultOutcome::Winner) {
                $winners[] = $winnerOrReserveRow;
            } else {
                $reserves[] = $winnerOrReserveRow;
            }
        }

        return [
            'program_id' => $programId->value,
            'winners' => $winners,
            'reserves' => $reserves,
            'ranks' => $ranks,
        ];
    }
}
