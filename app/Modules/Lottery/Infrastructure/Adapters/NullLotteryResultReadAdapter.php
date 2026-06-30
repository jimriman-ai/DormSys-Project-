<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Adapters;

use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;

final class NullLotteryResultReadAdapter implements LotteryResultReadContract
{
    public function resultsForProgram(LotteryProgramId $programId): array
    {
        return [
            'program_id' => $programId->value,
            'winners' => [],
            'reserves' => [],
            'ranks' => [],
        ];
    }
}
