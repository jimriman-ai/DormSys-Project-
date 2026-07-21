<?php

declare(strict_types=1);

namespace App\Integrations\Workflow;

use App\Modules\Request\Application\Contracts\RequestApprovalRepositoryContract;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\RequestApproval;
use App\Modules\Request\Domain\Enums\ApprovalDecision;
use App\Modules\Request\Domain\Enums\ApprovalStage;
use App\Modules\Request\Domain\Events\RequestApprovalRecorded;
use App\Modules\Request\Domain\Events\RequestApproved;
use App\Modules\Request\Domain\Events\RequestRejected;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\Services\ApprovalStageResolver;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Workflow\Application\Contracts\RequestApprovalCommandPort;
use DateTimeImmutable;
use Illuminate\Support\Facades\Event;

/**
 * Workflow → Request command bridge (CD-010 / OD-3).
 * Appends canonical RequestApproval and advances Request lifecycle; does not own orchestration.
 */
final class RequestApprovalCommandBridge implements RequestApprovalCommandPort
{
    public function __construct(
        private readonly RequestRepositoryContract $requests,
        private readonly RequestApprovalRepositoryContract $approvals,
        private readonly ApprovalStageResolver $stageResolver,
    ) {}

    public function recordStageApproved(
        string $requestId,
        string $stage,
        string $approverIdentityId,
        DateTimeImmutable $decidedAt,
    ): void {
        $request = $this->requirePendingRequest($requestId);
        $approvalStage = $this->requireMatchingStage($request->status, $stage);

        $approverId = ApproverReferenceId::fromString($approverIdentityId);
        $this->approvals->append(new RequestApproval(
            id: null,
            requestId: $request->requireId(),
            stage: $approvalStage,
            decision: ApprovalDecision::Approved,
            approverId: $approverId,
            reason: null,
            decidedAt: $decidedAt,
        ));

        Event::dispatch(RequestApprovalRecorded::forApproval(
            requestId: $request->requireId()->value,
            approvalPayload: [
                'stage' => $approvalStage->value,
                'decision' => ApprovalDecision::Approved->value,
                'approver_id' => $approverId->value,
                'reason' => null,
            ],
        ));

        $nextStatus = $this->stageResolver->statusAfterApproval($approvalStage);
        $advanced = $this->requests->save($request->withStatus($nextStatus));

        if ($nextStatus === ApprovedState::$name) {
            Event::dispatch(RequestApproved::forRequest($advanced->requireId()->value));
        }
    }

    public function recordStageRejected(
        string $requestId,
        string $stage,
        string $approverIdentityId,
        string $reason,
        DateTimeImmutable $decidedAt,
    ): void {
        $request = $this->requirePendingRequest($requestId);
        $approvalStage = $this->requireMatchingStage($request->status, $stage);

        $approverId = ApproverReferenceId::fromString($approverIdentityId);
        $this->approvals->append(new RequestApproval(
            id: null,
            requestId: $request->requireId(),
            stage: $approvalStage,
            decision: ApprovalDecision::Rejected,
            approverId: $approverId,
            reason: $reason,
            decidedAt: $decidedAt,
        ));

        Event::dispatch(RequestApprovalRecorded::forApproval(
            requestId: $request->requireId()->value,
            approvalPayload: [
                'stage' => $approvalStage->value,
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
    }

    private function requirePendingRequest(string $requestId): \App\Modules\Request\Domain\Entities\Request
    {
        $request = $this->requests->findById(RequestId::fromString($requestId));

        if ($request === null) {
            throw new RequestNotFoundException('Request not found.');
        }

        if (! $request->isPendingApproval()) {
            throw new InvalidRequestTransitionException('Request is not awaiting approval.');
        }

        return $request;
    }

    private function requireMatchingStage(string $requestStatus, string $stage): ApprovalStage
    {
        $current = $this->stageResolver->stageForStatus($requestStatus);

        if ($current === null) {
            throw new InvalidRequestTransitionException('Request is not awaiting approval.');
        }

        if ($current->value !== $stage) {
            throw new InvalidRequestTransitionException(
                sprintf('Workflow stage "%s" does not match request pending stage "%s".', $stage, $current->value),
            );
        }

        return $current;
    }
}
