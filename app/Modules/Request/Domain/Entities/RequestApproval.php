<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Entities;

use App\Modules\Request\Domain\Enums\ApprovalDecision;
use App\Modules\Request\Domain\Enums\ApprovalStage;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use DateTimeImmutable;

final class RequestApproval
{
    public function __construct(
        public readonly ?string $id,
        public readonly RequestId $requestId,
        public readonly ApprovalStage $stage,
        public readonly ApprovalDecision $decision,
        public readonly ApproverReferenceId $approverId,
        public readonly ?string $reason,
        public readonly DateTimeImmutable $decidedAt,
    ) {}
}
