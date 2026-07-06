<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Domain;

use App\Modules\Lottery\Domain\Exceptions\LotteryValidationException;
use App\Modules\Lottery\Domain\Models\EligibleSnapshot;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\Services\LockedLotterySemanticContract;
use App\Modules\Lottery\Domain\Services\LotteryScoringEngine;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotterySnapshotId;
use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LockedLotterySemanticContractTest extends TestCase
{
    #[Test]
    public function it_builds_a_versioned_lock_boundary_payload(): void
    {
        $config = $this->sampleConfig();
        $randomSeed = UuidGenerator::uuid7();

        $payload = LockedLotterySemanticContract::buildPayload(
            eligible: [],
            excluded: [],
            lockedAtIso: '2026-06-30T12:00:00+00:00',
            programCapacity: 5,
            randomSeed: $randomSeed,
            scoringConfig: $config,
        );

        expect($payload['semantic_contract_version'])->toBe(LockedLotterySemanticContract::PAYLOAD_VERSION);
        expect($payload['lock_boundary']['program_capacity'])->toBe(5);
        expect($payload['lock_boundary']['random_seed'])->toBe($randomSeed);
        expect($payload['lock_boundary']['scoring_config_version'])->toBe('1.0.0');
    }

    #[Test]
    public function it_rejects_draw_when_program_seed_does_not_match_snapshot(): void
    {
        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $program = new LotteryProgram(
            id: $programId,
            title: 'Mismatch Program',
            dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
            capacity: 1,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-31', new DateTimeZone('UTC')),
            status: LockedState::$name,
            randomSeed: UuidGenerator::uuid7(),
            scoringConfigVersion: '1.0.0',
        );

        $snapshot = new EligibleSnapshot(
            id: LotterySnapshotId::fromString(UuidGenerator::uuid7()),
            programId: $programId,
            payload: ['eligible' => [], 'excluded' => []],
            randomSeed: UuidGenerator::uuid7(),
            scoringConfig: $this->sampleConfig(),
        );

        $this->expectException(LotteryValidationException::class);
        $this->expectExceptionMessage('random seed');

        LockedLotterySemanticContract::assertProgramAligned($program, $snapshot);
    }

    #[Test]
    public function it_verifies_reproducible_weighted_scores_from_frozen_inputs(): void
    {
        $config = $this->sampleConfig();
        $randomSeed = UuidGenerator::uuid7();
        $registrationId = UuidGenerator::uuid7();
        $engine = app(LotteryScoringEngine::class);
        $weightedScore = $engine->computeWeightedScore($config, $randomSeed, $registrationId, 12.5, 3);

        $snapshot = new EligibleSnapshot(
            id: LotterySnapshotId::fromString(UuidGenerator::uuid7()),
            programId: LotteryProgramId::fromString(UuidGenerator::uuid7()),
            payload: ['eligible' => [], 'excluded' => []],
            randomSeed: $randomSeed,
            scoringConfig: $config,
        );

        $eligible = [
            LockedLotterySemanticContract::materializeEligibleRow(
                registrationId: $registrationId,
                requestId: UuidGenerator::uuid7(),
                employeeId: UuidGenerator::uuid7(),
                dormitoryId: UuidGenerator::uuid7(),
                baseScore: 12.5,
                departmentPriority: 3,
                weightedScore: $weightedScore,
            ),
        ];

        LockedLotterySemanticContract::assertScoresReproducible($eligible, $snapshot, $engine);

        $tampered = $eligible;
        $tampered[0]['weighted_score'] = $weightedScore + 0.01;

        $this->expectException(LotteryValidationException::class);
        $this->expectExceptionMessage('not reproducible');

        LockedLotterySemanticContract::assertScoresReproducible($tampered, $snapshot, $engine);
    }

    private function sampleConfig(): ScoringConfig
    {
        return new ScoringConfig(
            version: '1.0.0',
            baseScoreCoefficient: 1.0,
            departmentPriorityCoefficient: 0.05,
            normalizationDivisor: 100.0,
            prngScale: 1.0,
        );
    }
}
