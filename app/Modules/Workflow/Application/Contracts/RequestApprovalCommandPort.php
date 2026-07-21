<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\Contracts;

use DateTimeImmutable;

/**
 * Outbound port to Request Application — records canonical RequestApproval + status (CD-010 / OD-3).
 * Implemented in WP-WF-04 cutover; not implemented in WP-WF-02.
 */
interface RequestApprovalCommandPort
{
    public function recordStageApproved(
        string $requestId,
        string $stage,
        string $approverIdentityId,
        DateTimeImmutable $decidedAt,
    ): void;

    public function recordStageRejected(
        string $requestId,
        string $stage,
        string $approverIdentityId,
        string $reason,
        DateTimeImmutable $decidedAt,
    ): void;
}
