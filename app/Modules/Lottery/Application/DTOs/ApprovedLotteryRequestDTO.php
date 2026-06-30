<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\DTOs;

final readonly class ApprovedLotteryRequestDTO
{
    public function __construct(
        public string $requestId,
        public string $employeeId,
        public string $dormitoryId,
    ) {}
}
