<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

use App\Modules\Lottery\Application\DTOs\ApprovedLotteryRequestDTO;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;

interface LotteryRequestReadPort
{
    public function findApprovedLotteryRegistration(RequestReferenceId $requestId): ?ApprovedLotteryRequestDTO;
}
