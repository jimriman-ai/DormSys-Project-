<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\DependentSnapshotRepositoryContract;
use App\Modules\Request\Application\Contracts\DependentSnapshotSourceContract;
use App\Modules\Request\Application\Contracts\DormitoryReadContract;
use App\Modules\Request\Application\Contracts\Internal\RequestEligibilityGatewayContract;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Application\DTOs\DependentSnapshotReadDTO;
use App\Modules\Request\Domain\Entities\DependentSnapshot;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Enums\DependentRelationship;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\Events\RequestSubmitted;
use App\Modules\Request\Domain\Exceptions\RequestNotEligibleException;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class CreateFamilyDirectRequestAction
{
    public function __construct(
        private readonly RequestCodeGenerator $codeGenerator,
        private readonly RequestRepositoryContract $requests,
        private readonly DependentSnapshotSourceContract $dependentSnapshots,
        private readonly DependentSnapshotRepositoryContract $snapshotRepository,
        private readonly RequestEligibilityGatewayContract $eligibility,
        private readonly DormitoryReadContract $dormitoryRead,
        private readonly SubmitRequestAction $submitRequestAction,
    ) {}

    /**
     * @param  list<string>  $sourceDependentIds
     */
    public function execute(
        EmployeeReferenceId $employeeId,
        DormitorySiteId $dormitoryId,
        DateTimeImmutable $checkInDate,
        DateTimeImmutable $checkOutDate,
        array $sourceDependentIds,
    ): Request {
        if ($sourceDependentIds === []) {
            throw new RequestValidationException('FamilyDirect requests require at least one dependent snapshot.');
        }

        $draft = $this->requests->save(Request::createDraft(
            code: $this->codeGenerator->generate(),
            employeeId: $employeeId,
            dormitoryId: $dormitoryId,
            type: RequestType::FamilyDirect,
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

        $snapshotReads = $this->resolveSnapshots(
            employeeId: $draft->employeeId->value,
            sourceDependentIds: $sourceDependentIds,
        );

        $submittedAt = now('UTC')->toDateTimeImmutable();
        $submitted = $draft->markSubmitted($submittedAt);

        return DB::transaction(function () use ($submitted, $snapshotReads): Request {
            $persisted = $this->requests->save($submitted);
            $capturedAt = now('UTC')->toDateTimeImmutable();

            foreach ($snapshotReads as $snapshotRead) {
                $this->snapshotRepository->append(new DependentSnapshot(
                    id: null,
                    requestId: $persisted->requireId(),
                    sourceDependentId: $snapshotRead->sourceDependentId,
                    firstName: $snapshotRead->firstName,
                    lastName: $snapshotRead->lastName,
                    relationship: DependentRelationship::from($snapshotRead->relationship),
                    nationalCode: $snapshotRead->nationalCode,
                    capturedAt: $capturedAt,
                ));
            }

            Event::dispatch(RequestSubmitted::forRequest(
                requestId: $persisted->requireId()->value,
                employeeId: $persisted->employeeId->value,
                status: $persisted->status,
            ));

            return $persisted;
        });
    }

    /**
     * @param  list<string>  $sourceDependentIds
     * @return list<DependentSnapshotReadDTO>
     */
    private function resolveSnapshots(string $employeeId, array $sourceDependentIds): array
    {
        $resolved = [];

        foreach ($sourceDependentIds as $sourceDependentId) {
            $snapshot = $this->dependentSnapshots->findSnapshotForDependent($employeeId, $sourceDependentId);

            if ($snapshot === null) {
                throw new RequestValidationException('Dependent snapshot source was not found.');
            }

            if ($snapshot->ownerEmployeeId !== $employeeId) {
                throw new RequestValidationException('Dependent does not belong to the submitting employee.');
            }

            if (! $snapshot->eligible) {
                throw new RequestValidationException('Dependent is not eligible for FamilyDirect requests.');
            }

            $resolved[] = $snapshot;
        }

        return $resolved;
    }
}
