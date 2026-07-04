<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\DormitoryReadContract;
use App\Modules\Request\Application\Contracts\Internal\RequestEligibilityGatewayContract;
use App\Modules\Request\Application\Contracts\MissionDetailsRepositoryContract;
use App\Modules\Request\Application\Contracts\RequestMemberRepositoryContract;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\MissionDetails;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Entities\RequestMember;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\Events\RequestSubmitted;
use App\Modules\Request\Domain\Exceptions\RequestNotEligibleException;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;
use App\Modules\Request\Domain\Services\MissionGroupValidator;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class CreateMissionRequestAction
{
    public function __construct(
        private readonly RequestCodeGenerator $codeGenerator,
        private readonly RequestRepositoryContract $requests,
        private readonly RequestMemberRepositoryContract $members,
        private readonly MissionDetailsRepositoryContract $missionDetails,
        private readonly MissionGroupValidator $groupValidator,
        private readonly RequestEligibilityGatewayContract $eligibility,
        private readonly DormitoryReadContract $dormitoryRead,
        private readonly SubmitRequestAction $submitRequestAction,
    ) {}

    /**
     * @param  list<array{employeeId: string, isLeader: bool}>  $members
     */
    public function execute(
        EmployeeReferenceId $employeeId,
        DormitorySiteId $dormitoryId,
        DateTimeImmutable $checkInDate,
        DateTimeImmutable $checkOutDate,
        array $members,
        string $description,
        ?string $missionDocumentUrl = null,
    ): Request {
        if (trim($description) === '') {
            throw new RequestValidationException('Mission requests require a description.');
        }

        $this->groupValidator->validate($members);

        $draft = $this->requests->save(Request::createDraft(
            code: $this->codeGenerator->generate(),
            employeeId: $employeeId,
            dormitoryId: $dormitoryId,
            type: RequestType::Mission,
            checkInDate: $checkInDate,
            checkOutDate: $checkOutDate,
        ));

        $this->submitRequestAction->validateDates($draft);

        $eligibility = $this->eligibility->computeRequestEligibility(
            $draft->employeeId->value,
            $draft->requireId()->value,
        );

        if (! $eligibility->eligible) {
            throw new RequestNotEligibleException(
                reasonCodes: $eligibility->reasonCodes,
            );
        }

        if (! $this->dormitoryRead->siteExists($draft->dormitoryId->value)) {
            throw new RequestValidationException('Dormitory site does not exist.');
        }

        $submittedAt = now('UTC')->toDateTimeImmutable();
        $submitted = $draft->markSubmitted($submittedAt);

        return DB::transaction(function () use ($submitted, $members, $description, $missionDocumentUrl): Request {
            $persisted = $this->requests->save($submitted);

            foreach ($members as $member) {
                $this->members->append(new RequestMember(
                    id: null,
                    requestId: $persisted->requireId(),
                    employeeId: $member['employeeId'],
                    isLeader: $member['isLeader'],
                ));
            }

            $this->missionDetails->save(new MissionDetails(
                requestId: $persisted->requireId(),
                description: $description,
                missionDocumentUrl: $missionDocumentUrl,
            ));

            Event::dispatch(RequestSubmitted::forRequest(
                requestId: $persisted->requireId()->value,
                employeeId: $persisted->employeeId->value,
                status: $persisted->status,
            ));

            return $persisted;
        });
    }
}
