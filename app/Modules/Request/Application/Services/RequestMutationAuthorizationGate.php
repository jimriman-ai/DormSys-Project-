<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Services\ApprovalStageResolver;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;

final class RequestMutationAuthorizationGate
{
    public function __construct(
        private readonly MutationPrincipalContextPort $principalContext,
        private readonly EmployeeRepositoryContract $employees,
        private readonly ApprovalStageResolver $stageResolver,
    ) {}

    public function assertSubmitOwn(Request $request): void
    {
        $this->assertOwnerMatchesRequest($request);
    }

    public function assertCancelOwn(Request $request): void
    {
        $this->assertOwnerMatchesRequest($request);
    }

    public function assertApprove(Request $request, ApproverReferenceId $approverId): void
    {
        $this->assertApproverMatchesActor($approverId);
        $this->assertCurrentApprovalStage($request);
    }

    public function assertReject(Request $request, ApproverReferenceId $approverId): void
    {
        $this->assertApproverMatchesActor($approverId);
        $this->assertCurrentApprovalStage($request);
    }

    private function assertOwnerMatchesRequest(Request $request): void
    {
        $principalId = $this->requirePrincipalId();
        $employee = $this->employees->findByIdentityId(IdentityUserId::fromString($principalId));

        if ($employee === null || $employee->requireId()->value !== $request->employeeId->value) {
            throw new UnauthorizedMutationException('Mutation actor must own the request.');
        }
    }

    private function assertApproverMatchesActor(ApproverReferenceId $approverId): void
    {
        $principalId = $this->requirePrincipalId();

        if ($principalId !== $approverId->value) {
            throw new UnauthorizedMutationException('Approver must match the mutation actor.');
        }
    }

    private function assertCurrentApprovalStage(Request $request): void
    {
        if ($this->stageResolver->stageForStatus($request->status) === null) {
            throw new UnauthorizedMutationException('Request is not awaiting approval at the current stage.');
        }
    }

    private function requirePrincipalId(): string
    {
        $principalId = $this->principalContext->currentPrincipalId();

        if ($principalId === null || $principalId === '') {
            throw new UnauthorizedMutationException('Mutation requires an authorized principal.');
        }

        return $principalId;
    }
}
