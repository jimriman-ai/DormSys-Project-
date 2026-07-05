<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Request\Application\Contracts\RequestApprovalRepositoryContract;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Entities\RequestApproval;
use App\Modules\Request\Domain\Enums\ApprovalDecision;
use App\Modules\Request\Domain\Enums\ApprovalStage;
use App\Modules\Request\Domain\Events\RequestApprovalRecorded;
use App\Modules\Request\Domain\Events\RequestApproved;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\Services\ApprovalStageResolver;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Shared\ValueObjects\SystemActorId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class ApproveRequestStageAction
{
    public function __construct(
        private readonly RequestRepositoryContract $requests,
        private readonly RequestApprovalRepositoryContract $approvals,
        private readonly ApprovalStageResolver $stageResolver,
        private readonly AutoApprovalSettingsReader $autoApproval,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly RequestMutationAuthorizationGate $requestMutationAuth,
    ) {}

    public function execute(RequestId $requestId, ApproverReferenceId $approverId): Request
    {
        $request = $this->loadPendingRequest($requestId);
        $stage = $this->stageResolver->stageForStatus($request->status);

        $this->mutationPolicy->enforce(MutationCapabilityCatalog::REQUEST_APPROVE, [
            'requestId' => $requestId->value,
            'approverId' => $approverId->value,
            'stage' => $stage?->value,
        ]);
        $this->requestMutationAuth->assertApprove($request, $approverId);

        return DB::transaction(function () use ($request, $approverId): Request {
            $request = $this->recordApprovalAndAdvance($request, $approverId);

            return $this->applyAutoApprovalChain($request);
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

    private function recordApprovalAndAdvance(Request $request, ApproverReferenceId $approverId): Request
    {
        $stage = $this->stageResolver->stageForStatus($request->status);

        if ($stage === null) {
            throw new InvalidRequestTransitionException('Request is not awaiting approval.');
        }

        $decidedAt = now('UTC')->toDateTimeImmutable();
        $this->approvals->append(new RequestApproval(
            id: null,
            requestId: $request->requireId(),
            stage: $stage,
            decision: ApprovalDecision::Approved,
            approverId: $approverId,
            reason: null,
            decidedAt: $decidedAt,
        ));

        $this->dispatchApprovalRecorded($request, $stage, ApprovalDecision::Approved, $approverId, null);

        $nextStatus = $this->stageResolver->statusAfterApproval($stage);
        $advanced = $this->requests->save($request->withStatus($nextStatus));

        if ($nextStatus === ApprovedState::$name) {
            Event::dispatch(RequestApproved::forRequest($advanced->requireId()->value));
        }

        return $advanced;
    }

    private function applyAutoApprovalChain(Request $request): Request
    {
        while ($request->isPendingApproval()) {
            $stage = $this->stageResolver->stageForStatus($request->status);

            if ($stage === null || ! $this->autoApproval->isEnabled($stage)) {
                break;
            }

            $request = $this->recordApprovalAndAdvance(
                $request,
                ApproverReferenceId::fromString(SystemActorId::VALUE),
            );
        }

        return $request;
    }

    private function dispatchApprovalRecorded(
        Request $request,
        ApprovalStage $stage,
        ApprovalDecision $decision,
        ApproverReferenceId $approverId,
        ?string $reason,
    ): void {
        Event::dispatch(RequestApprovalRecorded::forApproval(
            requestId: $request->requireId()->value,
            approvalPayload: [
                'stage' => $stage->value,
                'decision' => $decision->value,
                'approver_id' => $approverId->value,
                'reason' => $reason,
            ],
        ));
    }
}
