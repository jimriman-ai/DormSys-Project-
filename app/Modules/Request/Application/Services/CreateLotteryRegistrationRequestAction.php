<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Create draft lottery-registration request with Stage-1 approver snapshot.
 *
 * Same approval workflow as personal requests: Stage-1 snapshot is required on create
 * so submit can start Workflow with an assigned first-stage actor.
 */
final class CreateLotteryRegistrationRequestAction
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
        return DB::transaction(function () use ($employeeId, $dormitoryId, $checkInDate, $checkOutDate): Request {
            $stage1ApproverIdentityId = $this->assignStage1Approver->execute();

            $request = Request::createDraft(
                code: $this->codeGenerator->generate(),
                employeeId: $employeeId,
                dormitoryId: $dormitoryId,
                type: RequestType::LotteryRegistration,
                checkInDate: $checkInDate,
                checkOutDate: $checkOutDate,
                assignedStage1ApproverIdentityId: $stage1ApproverIdentityId,
            );

            return $this->requests->save($request);
        });
    }
}
