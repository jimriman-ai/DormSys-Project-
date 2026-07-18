<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use DateTimeImmutable;

/**
 * [PERMIT-ID: IMPL-PERMIT-02] Create draft personal request with Stage-1 approver snapshot.
 *
 * Identity role assertion (employee) is enforced at the Presentation boundary via
 * IdentityRoleGuard / identity.role middleware — not Auth::user() here.
 */
final class CreatePersonalRequestAction
{
    public function __construct(
        private readonly RequestCodeGenerator $codeGenerator,
        private readonly RequestRepositoryContract $requests,
        private readonly AssignStage1ApproverSnapshotAction $assignStage1Approver,
    ) {}

    public function execute(
        EmployeeReferenceId $employeeId,
        DormitorySiteId $dormitoryId,
        DateTimeImmutable $checkInDate,
        DateTimeImmutable $checkOutDate,
    ): Request {
        $stage1ApproverIdentityId = $this->assignStage1Approver->execute($employeeId);

        $request = Request::createDraft(
            code: $this->codeGenerator->generate(),
            employeeId: $employeeId,
            dormitoryId: $dormitoryId,
            type: RequestType::Personal,
            checkInDate: $checkInDate,
            checkOutDate: $checkOutDate,
            assignedStage1ApproverIdentityId: $stage1ApproverIdentityId,
        );

        return $this->requests->save($request);
    }
}
