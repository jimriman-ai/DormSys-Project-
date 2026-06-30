<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Domain;

use App\Modules\Lottery\Domain\Services\LotteryScoringEngine;
use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryScoringEngineTest extends TestCase
{
    #[Test]
    public function it_produces_identical_scores_for_identical_inputs(): void
    {
        $engine = new LotteryScoringEngine();
        $config = new ScoringConfig(
            version: '1.0.0',
            baseScoreCoefficient: 1.0,
            departmentPriorityCoefficient: 0.05,
            normalizationDivisor: 100.0,
            prngScale: 1.0,
        );
        $seed = 'seed-abc-123';
        $registrationId = UuidGenerator::uuid7();

        $first = $engine->computeWeightedScore(
            config: $config,
            randomSeed: $seed,
            registrationId: $registrationId,
            baseScore: 42.0,
            departmentPriority: 3,
        );

        $second = $engine->computeWeightedScore(
            config: $config,
            randomSeed: $seed,
            registrationId: $registrationId,
            baseScore: 42.0,
            departmentPriority: 3,
        );

        expect($first)->toBe($second);
    }

    #[Test]
    public function it_changes_scores_when_random_seed_changes(): void
    {
        $engine = new LotteryScoringEngine();
        $config = new ScoringConfig(
            version: '1.0.0',
            baseScoreCoefficient: 1.0,
            departmentPriorityCoefficient: 0.0,
            normalizationDivisor: 1.0,
            prngScale: 1.0,
        );
        $registrationId = UuidGenerator::uuid7();

        $first = $engine->computeWeightedScore($config, 'seed-a', $registrationId, 10.0, 0);
        $second = $engine->computeWeightedScore($config, 'seed-b', $registrationId, 10.0, 0);

        expect($first)->not->toBe($second);
    }

    #[Test]
    public function it_changes_scores_when_registration_id_changes(): void
    {
        $engine = new LotteryScoringEngine();
        $config = new ScoringConfig(
            version: '1.0.0',
            baseScoreCoefficient: 1.0,
            departmentPriorityCoefficient: 0.0,
            normalizationDivisor: 1.0,
            prngScale: 1.0,
        );
        $seed = 'fixed-seed';

        $first = $engine->computeWeightedScore($config, $seed, UuidGenerator::uuid7(), 10.0, 0);
        $second = $engine->computeWeightedScore($config, $seed, UuidGenerator::uuid7(), 10.0, 0);

        expect($first)->not->toBe($second);
    }
}
