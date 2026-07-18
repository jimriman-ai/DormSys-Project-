<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\Stage1ApproverIdentityReadContract;
use App\Modules\Request\Domain\Exceptions\Stage1ApproverUnresolvedException;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;

/**
 * [PERMIT-ID: IMPL-PERMIT-02] Resolve Stage-1 identity for create-time snapshot (IMP-Q-02).
 */
final class AssignStage1ApproverSnapshotAction
{
    public function __construct(
        private readonly Stage1ApproverIdentityReadContract $stage1ApproverIdentity,
    ) {}

    /**
     * @return non-empty-string
     */
    public function execute(EmployeeReferenceId $employeeId): string
    {
        $identityId = $this->stage1ApproverIdentity->resolveForEmployee($employeeId->value);

        if ($identityId === null || $identityId === '') {
            throw new Stage1ApproverUnresolvedException;
        }

        return $identityId;
    }
}
