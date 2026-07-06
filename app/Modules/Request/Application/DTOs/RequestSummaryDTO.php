<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\DTOs;

final readonly class RequestSummaryDTO
{
    public function __construct(
        public string $id,
        public string $code,
        public string $employeeId,
        public string $dormitoryId,
        public string $type,
        public string $status,
        public string $checkInDate,
        public string $checkOutDate,
        public ?string $submittedAt,
        public ?string $cancelledAt = null,
        public ?string $rejectionReason = null,
        public ?int $memberCount = null,
        public ?int $dependentCount = null,
    ) {}
}
