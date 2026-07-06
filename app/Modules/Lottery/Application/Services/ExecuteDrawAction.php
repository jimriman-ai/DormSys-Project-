<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Lottery\Application\Contracts\LotteryEligibleSnapshotRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryResultRepositoryContract;
use App\Modules\Lottery\Application\Contracts\ProposedAllocationPort;
use App\Modules\Lottery\Domain\Enums\LotteryResultOutcome;
use App\Modules\Lottery\Domain\Events\LotteryProgramStateChanged;
use App\Modules\Lottery\Domain\Exceptions\DrawNotAllowedException;
use App\Modules\Lottery\Domain\Exceptions\EligibleSnapshotNotFoundException;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\Models\LotteryResult;
use App\Modules\Lottery\Domain\Services\LockedLotterySemanticContract;
use App\Modules\Lottery\Domain\Services\LotteryDrawSelector;
use App\Modules\Lottery\Domain\Services\LotteryScoringEngine;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryRegistrationId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class ExecuteDrawAction
{
    public function __construct(
        private readonly LotteryProgramRepositoryContract $programs,
        private readonly LotteryEligibleSnapshotRepositoryContract $snapshots,
        private readonly LotteryResultRepositoryContract $results,
        private readonly LotteryDrawSelector $drawSelector,
        private readonly LotteryScoringEngine $scoringEngine,
        private readonly ProposedAllocationPort $proposedAllocations,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly LotteryMutationAuthorizationGate $lotteryMutationAuth,
    ) {}

    public function execute(LotteryProgramId $programId): LotteryProgram
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::LOTTERY_PROGRAM_DRAW, [
            'programId' => $programId->value,
        ]);
        $this->lotteryMutationAuth->assertManageProgram();

        $program = $this->programs->findById($programId);

        if ($program === null) {
            throw new LotteryProgramNotFoundException('Lottery program not found.');
        }

        if ($this->results->existsForProgram($programId)) {
            return $this->programs->findById($programId)
                ?? throw new LotteryProgramNotFoundException('Lottery program not found.');
        }

        if (! $program->canDraw()) {
            throw new DrawNotAllowedException('Only locked programs can be drawn.');
        }

        $snapshot = $this->snapshots->findByProgramId($programId);

        if ($snapshot === null) {
            throw new EligibleSnapshotNotFoundException('Eligible snapshot not found for locked program.');
        }

        LockedLotterySemanticContract::assertProgramAligned($program, $snapshot);

        /** @var list<array<string, mixed>> $eligible */
        $eligible = $snapshot->payload['eligible'] ?? [];
        LockedLotterySemanticContract::assertScoresReproducible($eligible, $snapshot, $this->scoringEngine);

        if ($eligible === []) {
            return $this->completeDrawWithoutResults($program);
        }

        $drawnAt = now('UTC')->toDateTimeImmutable();

        return DB::transaction(function () use ($program, $programId, $snapshot, $drawnAt): LotteryProgram {
            if ($this->results->existsForProgram($programId)) {
                return $this->programs->findById($programId)
                    ?? throw new LotteryProgramNotFoundException('Lottery program not found.');
            }

            $selections = $this->drawSelector->select(
                $program->capacity,
                LockedLotterySemanticContract::drawEligibleRows($snapshot),
            );
            $winnerPayload = [];

            foreach ($selections as $selection) {
                $result = LotteryResult::create(
                    programId: $programId,
                    registrationId: LotteryRegistrationId::fromString($selection['registration_id']),
                    rank: $selection['rank'],
                    outcome: $selection['outcome'],
                );
                $this->results->save($result);

                if ($selection['outcome'] === LotteryResultOutcome::Winner) {
                    $winnerPayload[] = [
                        'program_id' => $programId->value,
                        'registration_id' => $selection['registration_id'],
                        'employee_id' => $selection['employee_id'],
                        'dormitory_id' => $program->dormitoryId->value,
                        'rank' => $selection['rank'],
                    ];
                }
            }

            $previousStatus = $program->status;
            $drawn = $program->markDrawn($drawnAt);
            $persistedDrawn = $this->programs->save($drawn);

            Event::dispatch(LotteryProgramStateChanged::forProgram(
                programId: $persistedDrawn->requireId()->value,
                previousStatus: $previousStatus,
                newStatus: $persistedDrawn->status,
            ));

            $completed = $persistedDrawn->markCompleted();
            $persistedCompleted = $this->programs->save($completed);

            Event::dispatch(LotteryProgramStateChanged::forProgram(
                programId: $persistedCompleted->requireId()->value,
                previousStatus: $persistedDrawn->status,
                newStatus: $persistedCompleted->status,
            ));

            if ($winnerPayload !== []) {
                $this->proposedAllocations->emitProposedAllocations($winnerPayload);
            }

            return $persistedCompleted;
        });
    }

    private function completeDrawWithoutResults(LotteryProgram $program): LotteryProgram
    {
        $drawnAt = now('UTC')->toDateTimeImmutable();

        return DB::transaction(function () use ($program, $drawnAt): LotteryProgram {
            $previousStatus = $program->status;
            $drawn = $program->markDrawn($drawnAt);
            $persistedDrawn = $this->programs->save($drawn);

            Event::dispatch(LotteryProgramStateChanged::forProgram(
                programId: $persistedDrawn->requireId()->value,
                previousStatus: $previousStatus,
                newStatus: $persistedDrawn->status,
            ));

            $completed = $persistedDrawn->markCompleted();
            $persistedCompleted = $this->programs->save($completed);

            Event::dispatch(LotteryProgramStateChanged::forProgram(
                programId: $persistedCompleted->requireId()->value,
                previousStatus: $persistedDrawn->status,
                newStatus: $persistedCompleted->status,
            ));

            return $persistedCompleted;
        });
    }
}
