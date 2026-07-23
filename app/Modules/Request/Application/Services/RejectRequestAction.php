<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;
use App\Modules\Request\Domain\Services\ApprovalStageResolver;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Workflow\Application\DTOs\DecideRequestApprovalStageCommand;
use App\Modules\Workflow\Application\Exceptions\InvalidWorkflowTransitionException;
use App\Modules\Workflow\Application\Exceptions\UnauthorizedWorkflowStageActorException;
use App\Modules\Workflow\Application\Exceptions\WorkflowInstanceNotFoundException;
use App\Modules\Workflow\Application\Services\DecideRequestApprovalStageAction;
use Illuminate\Support\Facades\DB;

/**
 * Rejects the current Request stage via Workflow orchestration (WP-WF-04 cutover).
 */
final class RejectRequestAction
{
    public function __construct(
        private readonly RequestRepositoryContract $requests,
        private readonly ApprovalStageResolver $stageResolver,
        private readonly DecideRequestApprovalStageAction $decideWorkflowStage,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly RequestMutationAuthorizationGate $requestMutationAuth,
    ) {}

    public function execute(RequestId $requestId, ApproverReferenceId $approverId, string $reason): Request
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw new RequestValidationException('Rejection reason is required.');
        }

        $request = $this->loadPendingRequest($requestId);
        $stage = $this->stageResolver->stageForStatus($request->status);

        $this->mutationPolicy->enforce(MutationCapabilityCatalog::REQUEST_REJECT, [
            'requestId' => $requestId->value,
            'approverId' => $approverId->value,
            'stage' => $stage?->value,
        ]);
        $this->requestMutationAuth->assertReject($request, $approverId);

        return DB::transaction(function () use ($requestId, $approverId, $reason): Request {
            try {
                $this->decideWorkflowStage->execute(new DecideRequestApprovalStageCommand(
                    requestId: $requestId->value,
                    actorIdentityId: $approverId->value,
                    decision: 'rejected',
                    reason: $reason,
                ));
            } catch (UnauthorizedWorkflowStageActorException $exception) {
                throw new InvalidRequestTransitionException($exception->getMessage(), previous: $exception);
            } catch (WorkflowInstanceNotFoundException $exception) {
                throw new InvalidRequestTransitionException($exception->getMessage(), previous: $exception);
            } catch (InvalidWorkflowTransitionException $exception) {
                throw new RequestValidationException($exception->getMessage(), previous: $exception);
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
