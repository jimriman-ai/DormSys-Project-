<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

use App\Modules\Lottery\Domain\Models\LotteryResult;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryResultId;

interface LotteryResultRepositoryContract
{
    public function save(LotteryResult $result): LotteryResult;

    public function findById(LotteryResultId $id): ?LotteryResult;

    /**
     * @return list<LotteryResult>
     */
    public function findByProgramId(LotteryProgramId $programId): array;

    public function existsForProgram(LotteryProgramId $programId): bool;
}
