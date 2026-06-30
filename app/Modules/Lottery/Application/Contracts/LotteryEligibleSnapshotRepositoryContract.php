<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

use App\Modules\Lottery\Domain\Models\EligibleSnapshot;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;

interface LotteryEligibleSnapshotRepositoryContract
{
    public function save(EligibleSnapshot $snapshot): EligibleSnapshot;

    public function findByProgramId(LotteryProgramId $programId): ?EligibleSnapshot;
}
