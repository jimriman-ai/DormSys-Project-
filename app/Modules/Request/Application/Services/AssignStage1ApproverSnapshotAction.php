<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\Stage1ApproverIdentityReadContract;
use App\Modules\Request\Domain\Exceptions\NoStage1ApproverAvailableException;

/**
 * [PERMIT-ID: IMPL-PERMIT-02] Resolve Dormitory Manager identity for create-time snapshot.
 */
final class AssignStage1ApproverSnapshotAction
{
    public function __construct(
        private readonly Stage1ApproverIdentityReadContract $stage1ApproverIdentity,
    ) {}

    /**
     * @return non-empty-string
     */
    public function execute(): string
    {
        $identityId = $this->stage1ApproverIdentity->resolveActiveDormitoryManagerIdentityId();

        if ($identityId === null || $identityId === '') {
            throw new NoStage1ApproverAvailableException;
        }

        return $identityId;
    }
}
