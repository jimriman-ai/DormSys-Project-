<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRequestReadPort;
use App\Modules\Lottery\Domain\Events\LotteryRegistrationCreated;
use App\Modules\Lottery\Domain\Exceptions\DuplicateEnrollmentException;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\Exceptions\LotteryValidationException;
use App\Modules\Lottery\Domain\Exceptions\RegistrationClosedException;
use App\Modules\Lottery\Domain\Models\LotteryRegistration;
use App\Modules\Lottery\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class EnrollRegistrationAction
{
    public function __construct(
        private readonly LotteryProgramRepositoryContract $programs,
        private readonly LotteryRegistrationRepositoryContract $registrations,
        private readonly LotteryRequestReadPort $requests,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly LotteryMutationAuthorizationGate $lotteryMutationAuth,
    ) {}

    public function execute(
        LotteryProgramId $programId,
        RequestReferenceId $requestId,
    ): LotteryRegistration {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::LOTTERY_ENROLL_OWN, [
            'programId' => $programId->value,
            'requestId' => $requestId->value,
        ]);

        $approvedRequest = $this->requests->findApprovedLotteryRegistration($requestId);

        if ($approvedRequest === null) {
            throw new LotteryValidationException('Approved lottery registration request not found.');
        }

        $this->lotteryMutationAuth->assertEnrollOwn($approvedRequest->employeeId);

        $program = $this->programs->findById($programId);

        if ($program === null) {
            throw new LotteryProgramNotFoundException('Lottery program not found.');
        }

        if (! $program->canAcceptEnrollment()) {
            throw new RegistrationClosedException('Registration is not open for this program.');
        }

        if ($approvedRequest->dormitoryId !== $program->dormitoryId->value) {
            throw new LotteryValidationException('Request dormitory does not match program dormitory.');
        }

        $enrolledAt = now('UTC')->toDateTimeImmutable();

        return DB::transaction(function () use ($programId, $requestId, $approvedRequest, $enrolledAt): LotteryRegistration {
            $lockedProgram = $this->programs->findByIdForUpdate($programId);

            if ($lockedProgram === null) {
                throw new LotteryProgramNotFoundException('Lottery program not found.');
            }

            if (! $lockedProgram->canAcceptEnrollment()) {
                throw new RegistrationClosedException('Registration is not open for this program.');
            }

            if ($this->registrations->findByProgramAndRequest($programId, $requestId) !== null) {
                throw new DuplicateEnrollmentException('Request is already enrolled in this program.');
            }

            $registration = LotteryRegistration::enroll(
                programId: $programId,
                requestId: $requestId,
                employeeId: EmployeeReferenceId::fromString($approvedRequest->employeeId),
                enrolledAt: $enrolledAt,
            );

            $persisted = $this->registrations->save($registration);

            Event::dispatch(LotteryRegistrationCreated::forRegistration(
                registrationId: $persisted->requireId()->value,
                programId: $persisted->programId->value,
                requestId: $persisted->requestId->value,
                employeeId: $persisted->employeeId->value,
            ));

            return $persisted;
        });
    }
}
