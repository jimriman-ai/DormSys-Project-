<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;

interface LotteryProgramRepositoryContract
{
    public function save(LotteryProgram $program): LotteryProgram;

    public function findById(LotteryProgramId $id): ?LotteryProgram;
}
