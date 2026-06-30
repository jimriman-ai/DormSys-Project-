<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

use App\Modules\Lottery\Application\Contracts\LotteryResultRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;

final class LotteryResultReadService implements LotteryResultReadContract
{
    public function __construct(
        private readonly LotteryResultRepositoryContract $results,
    ) {}

    public function resultsForProgram(LotteryProgramId $programId): array
    {
        return array_map(
            static fn ($result): array => [
                'registration_id' => $result->registrationId->value,
                'program_id' => $result->programId->value,
                'rank' => $result->rank,
                'outcome' => $result->outcome->value,
            ],
            $this->results->findByProgramId($programId),
        );
    }
}
