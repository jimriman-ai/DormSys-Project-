<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\DTOs;

final readonly class RequestApprovalHistoryDTO
{
    public function __construct(
        public string $stage,
        public string $decision,
        public string $approverId,
        public ?string $reason,
        public string $decidedAt,
    ) {}
}
