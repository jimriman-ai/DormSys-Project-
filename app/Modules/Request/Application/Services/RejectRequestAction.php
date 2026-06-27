<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\RequestApprovalRepositoryContract;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Entities\RequestApproval;
use App\Modules\Request\Domain\Enums\ApprovalDecision;
use App\Modules\Request\Domain\Events\RequestApprovalRecorded;
use App\Modules\Request\Domain\Events\RequestRejected;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;
use App\Modules\Request\Domain\Services\ApprovalStageResolver;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class RejectRequestAction
{
    public function __construct(
        private readonly RequestRepositoryContract $requests,
        private readonly RequestApprovalRepositoryContract $approvals,
        private readonly ApprovalStageResolver $stageResolver,
    ) {}

    public function execute(RequestId $requestId, ApproverReferenceId $approverId, string $reason): Request
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw new RequestValidationException('Rejection reason is required.');
        }

        return DB::transaction(function () use ($requestId, $approverId, $reason): Request {
            $request = $this->loadPendingRequest($requestId);
            $stage = $this->stageResolver->stageForStatus($request->status);

            if ($stage === null) {
                throw new InvalidRequestTransitionException('Request is not awaiting approval.');
            }

            $decidedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
            $this->approvals->append(new RequestApproval(
                id: null,
                requestId: $request->requireId(),
                stage: $stage,
                decision: ApprovalDecision::Rejected,
                approverId: $approverId,
                reason: $reason,
                decidedAt: $decidedAt,
            ));

            Event::dispatch(RequestApprovalRecorded::forApproval(
                requestId: $request->requireId()->value,
                approvalPayload: [
                    'stage' => $stage->value,
                    'decision' => ApprovalDecision::Rejected->value,
                    'approver_id' => $approverId->value,
                    'reason' => $reason,
                ],
            ));

            $rejected = $this->requests->save($request->markRejected($reason));

            Event::dispatch(RequestRejected::forRequest(
                requestId: $rejected->requireId()->value,
                reason: $reason,
            ));

            return $rejected;
        });
    }

    private function loadPendingRequest(RequestId $requestId): Request
    {
        $request = $this->requests->findById($requestId);

        if ($request === null) {
            throw new RequestNotFoundException('Request not found.');
        }

        if (! $request->isPendingApproval()) {
            throw new InvalidRequestTransitionException('Request is not awaiting approval.');
        }

        return $request;
    }
}
