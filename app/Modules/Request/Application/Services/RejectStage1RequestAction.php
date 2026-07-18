<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Shared\Auth\IdentityRoleGuard;

/**
 * [PERMIT-ID: IMPL-PERMIT-03] Stage-1 reject — IdentityRoleGuard first, then existing reject pipeline.
 *
 * Role: dormitory-manager (identity). DGAP-13 / Lead DGAP-09 scoped — aligns Stage-1 gates to IMPL-PERMIT-02 snapshot.
 */
final class RejectStage1RequestAction
{
    public function __construct(
        private readonly RequestRepositoryContract $requests,
        private readonly RejectRequestAction $rejectRequest,
    ) {}

    public function execute(RequestId $requestId, ApproverReferenceId $approverId, string $reason): Request
    {
        IdentityRoleGuard::assertDormitoryManager();

        $request = $this->requests->findById($requestId);

        if ($request === null) {
            throw new RequestNotFoundException('Request not found.');
        }

        if ($request->status !== PendingDepartmentManagerState::$name) {
            throw new InvalidRequestTransitionException('Request is not awaiting Stage-1 approval.');
        }

        return $this->rejectRequest->execute($requestId, $approverId, $reason);
    }
}
