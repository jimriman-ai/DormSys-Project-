<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

use App\Modules\Lottery\Application\DTOs\LotteryProgramSummaryDTO;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;

interface LotteryProgramReadContract
{
    public function getProgramSummary(LotteryProgramId $programId): ?LotteryProgramSummaryDTO;
}
