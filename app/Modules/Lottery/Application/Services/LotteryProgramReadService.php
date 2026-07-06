<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

use App\Modules\Lottery\Application\Contracts\LotteryProgramReadContract;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\DTOs\LotteryProgramSummaryDTO;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;

final class LotteryProgramReadService implements LotteryProgramReadContract
{
    public function __construct(
        private readonly LotteryProgramRepositoryContract $programs,
    ) {}

    public function getProgramSummary(LotteryProgramId $programId): ?LotteryProgramSummaryDTO
    {
        $program = $this->programs->findById($programId);

        if ($program === null) {
            return null;
        }

        return LotteryProgramSummaryDTO::fromProgram($program);
    }
}
