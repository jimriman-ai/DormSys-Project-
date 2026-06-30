<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

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
use App\Modules\Lottery\Domain\Services\LotteryDrawSelector;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryRegistrationId;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class ExecuteDrawAction
{
    public function __construct(
        private readonly LotteryProgramRepositoryContract $programs,
        private readonly LotteryEligibleSnapshotRepositoryContract $snapshots,
        private readonly LotteryResultRepositoryContract $results,
        private readonly LotteryDrawSelector $drawSelector,
        private readonly ProposedAllocationPort $proposedAllocations,
    ) {}

    public function execute(LotteryProgramId $programId): LotteryProgram
    {
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

        /** @var list<array<string, mixed>> $eligible */
        $eligible = $snapshot->payload['eligible'] ?? [];

        if ($eligible === []) {
            return $this->completeDrawWithoutResults($program);
        }

        $drawnAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        return DB::transaction(function () use ($program, $programId, $eligible, $drawnAt): LotteryProgram {
            if ($this->results->existsForProgram($programId)) {
                return $this->programs->findById($programId)
                    ?? throw new LotteryProgramNotFoundException('Lottery program not found.');
            }

            $selections = $this->drawSelector->select($program->capacity, $this->normalizeEligible($eligible));
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

    /**
     * @param  list<array<string, mixed>>  $eligible
     * @return list<array{registration_id: string, employee_id: string, weighted_score: float}>
     */
    private function normalizeEligible(array $eligible): array
    {
        return array_map(
            static fn (array $row): array => [
                'registration_id' => (string) $row['registration_id'],
                'employee_id' => (string) ($row['employee_id'] ?? ''),
                'weighted_score' => (float) ($row['weighted_score'] ?? 0.0),
            ],
            $eligible,
        );
    }

    private function completeDrawWithoutResults(LotteryProgram $program): LotteryProgram
    {
        $drawnAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

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
