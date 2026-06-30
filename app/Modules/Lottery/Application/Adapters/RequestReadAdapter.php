<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Adapters;

use App\Modules\Lottery\Application\Contracts\LotteryRequestReadPort;
use App\Modules\Lottery\Application\DTOs\ApprovedLotteryRequestDTO;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Request\Application\Contracts\RequestReadContract;

final class RequestReadAdapter implements LotteryRequestReadPort
{
    public function __construct(
        private readonly RequestReadContract $requests,
    ) {}

    public function findApprovedLotteryRegistration(RequestReferenceId $requestId): ?ApprovedLotteryRequestDTO
    {
        foreach ($this->requests->listApprovedByType('lottery_registration') as $summary) {
            if ($summary->id !== $requestId->value) {
                continue;
            }

            return new ApprovedLotteryRequestDTO(
                requestId: $summary->id,
                employeeId: $summary->employeeId,
                dormitoryId: $summary->dormitoryId,
            );
        }

        return null;
    }
}
