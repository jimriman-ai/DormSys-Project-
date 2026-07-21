<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\Services\ApprovalStageResolver;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Workflow\Application\DTOs\DecideRequestApprovalStageCommand;
use App\Modules\Workflow\Application\Services\DecideRequestApprovalStageAction;
use App\Modules\Workflow\Domain\Exceptions\UnauthorizedWorkflowStageActorException;
use App\Modules\Workflow\Domain\Exceptions\WorkflowInstanceNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Approves the current Request stage via Workflow orchestration (WP-WF-04 cutover).
 * Canonical history remains RequestApproval (via RequestApprovalCommandPort).
 */
final class ApproveRequestStageAction
{
    public function __construct(
        private readonly RequestRepositoryContract $requests,
        private readonly ApprovalStageResolver $stageResolver,
        private readonly DecideRequestApprovalStageAction $decideWorkflowStage,
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

        return DB::transaction(function () use ($requestId, $approverId): Request {
            try {
                $this->decideWorkflowStage->execute(new DecideRequestApprovalStageCommand(
                    requestId: $requestId->value,
                    actorIdentityId: $approverId->value,
                    decision: 'approved',
                ));
            } catch (UnauthorizedWorkflowStageActorException $exception) {
                throw new InvalidRequestTransitionException($exception->getMessage(), previous: $exception);
            } catch (WorkflowInstanceNotFoundException $exception) {
                throw new InvalidRequestTransitionException($exception->getMessage(), previous: $exception);
            }

            $updated = $this->requests->findById($requestId);

            if ($updated === null) {
                throw new RequestNotFoundException('Request not found.');
            }

            return $updated;
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
