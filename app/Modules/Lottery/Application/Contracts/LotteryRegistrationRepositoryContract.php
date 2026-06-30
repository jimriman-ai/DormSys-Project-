<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

use App\Modules\Lottery\Domain\Models\LotteryRegistration;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryRegistrationId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;

interface LotteryRegistrationRepositoryContract
{
    public function save(LotteryRegistration $registration): LotteryRegistration;

    public function findById(LotteryRegistrationId $id): ?LotteryRegistration;

    public function findByProgramAndRequest(
        LotteryProgramId $programId,
        RequestReferenceId $requestId,
    ): ?LotteryRegistration;
}
