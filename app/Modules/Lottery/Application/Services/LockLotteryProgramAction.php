<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Lottery\Application\Contracts\EmployeeLotteryScorePort;
use App\Modules\Lottery\Application\Contracts\LotteryEligibleSnapshotRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRequestReadPort;
use App\Modules\Lottery\Domain\Events\LotteryProgramStateChanged;
use App\Modules\Lottery\Domain\Exceptions\InvalidLotteryTransitionException;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\Models\EligibleSnapshot;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\Models\LotteryRegistration;
use App\Modules\Lottery\Domain\Services\LockedLotterySemanticContract;
use App\Modules\Lottery\Domain\Services\LotteryScoringEngine;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class LockLotteryProgramAction
{
    public function __construct(
        private readonly LotteryProgramRepositoryContract $programs,
        private readonly LotteryRegistrationRepositoryContract $registrations,
        private readonly LotteryEligibleSnapshotRepositoryContract $snapshots,
        private readonly LotteryRequestReadPort $requests,
        private readonly LotteryScoringConfigReader $scoringConfigReader,
        private readonly EmployeeLotteryScorePort $employeeScores,
        private readonly LotteryScoringEngine $scoringEngine,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly LotteryMutationAuthorizationGate $lotteryMutationAuth,
    ) {}

    public function execute(LotteryProgramId $programId): LotteryProgram
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::LOTTERY_PROGRAM_LOCK, [
            'programId' => $programId->value,
        ]);
        $this->lotteryMutationAuth->assertManageProgram();

        $program = $this->programs->findById($programId);

        if ($program === null) {
            throw new LotteryProgramNotFoundException('Lottery program not found.');
        }

        if (! $program->canLock()) {
            throw new InvalidLotteryTransitionException(
                'Only programs with closed registration can be locked.',
            );
        }

        if ($this->snapshots->findByProgramId($programId) !== null) {
            throw new InvalidLotteryTransitionException(
                'Eligible snapshot already captured for this program.',
            );
        }

        $config = $this->scoringConfigReader->load();
        $randomSeed = UuidGenerator::uuid7();
        $lockedAt = now('UTC')->toDateTimeImmutable();

        return DB::transaction(function () use ($program, $programId, $config, $randomSeed, $lockedAt): LotteryProgram {
            $previousStatus = $program->status;
            [$eligibleRows, $excludedRows, $scoredRegistrations] = $this->buildEligibleSnapshot(
                $programId,
                $config,
                $randomSeed,
            );

            foreach ($scoredRegistrations as $registration) {
                $this->registrations->save($registration);
            }

            $snapshot = EligibleSnapshot::capture(
                programId: $programId,
                payload: LockedLotterySemanticContract::buildPayload(
                    eligible: $eligibleRows,
                    excluded: $excludedRows,
                    lockedAtIso: $lockedAt->format(DATE_ATOM),
                    programCapacity: $program->capacity,
                    randomSeed: $randomSeed,
                    scoringConfig: $config,
                ),
                randomSeed: $randomSeed,
                scoringConfig: $config,
            );
            $this->snapshots->save($snapshot);

            $lockedProgram = $program->markLocked(
                randomSeed: $randomSeed,
                scoringConfigVersion: $config->version,
                lockedAt: $lockedAt,
            );
            $persisted = $this->programs->save($lockedProgram);

            Event::dispatch(LotteryProgramStateChanged::forProgram(
                programId: $persisted->requireId()->value,
                previousStatus: $previousStatus,
                newStatus: $persisted->status,
            ));

            return $persisted;
        });
    }

    /**
     * @return array{
     *     0: list<array<string, mixed>>,
     *     1: list<array<string, mixed>>,
     *     2: list<LotteryRegistration>
     * }
     */
    private function buildEligibleSnapshot(
        LotteryProgramId $programId,
        ScoringConfig $config,
        string $randomSeed,
    ): array {
        $eligibleRows = [];
        $excludedRows = [];
        $scoredRegistrations = [];

        foreach ($this->registrations->findByProgramId($programId) as $registration) {
            $approvedRequest = $this->requests->findApprovedLotteryRegistration($registration->requestId);

            if ($approvedRequest === null) {
                $excludedRows[] = [
                    'registration_id' => $registration->requireId()->value,
                    'request_id' => $registration->requestId->value,
                    'reason' => 'request_no_longer_approved',
                ];

                continue;
            }

            $baseScore = $this->employeeScores->baseScoreFor($registration->employeeId);
            $departmentPriority = $this->employeeScores->departmentPriorityFor($registration->employeeId);

            $weightedScore = $this->scoringEngine->computeWeightedScore(
                config: $config,
                randomSeed: $randomSeed,
                registrationId: $registration->requireId()->value,
                baseScore: $baseScore,
                departmentPriority: $departmentPriority,
            );

            $scored = $registration->withWeightedScore($weightedScore);
            $scoredRegistrations[] = $scored;

            $eligibleRows[] = LockedLotterySemanticContract::materializeEligibleRow(
                registrationId: $scored->requireId()->value,
                requestId: $scored->requestId->value,
                employeeId: $scored->employeeId->value,
                dormitoryId: $approvedRequest->dormitoryId,
                baseScore: $baseScore,
                departmentPriority: $departmentPriority,
                weightedScore: $weightedScore,
            );
        }

        return [$eligibleRows, $excludedRows, $scoredRegistrations];
    }
}
