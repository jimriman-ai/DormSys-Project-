<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\Services;

use App\Modules\Lottery\Domain\Exceptions\LotteryValidationException;
use App\Modules\Lottery\Domain\Models\EligibleSnapshot;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;

final class LockedLotterySemanticContract
{
    public const PAYLOAD_VERSION = '1.0.0';

    private const SCORE_EPSILON = 1e-8;

    /**
     * @return array<string, mixed>
     */
    public static function materializeEligibleRow(
        string $registrationId,
        string $requestId,
        string $employeeId,
        string $dormitoryId,
        float $baseScore,
        int $departmentPriority,
        float $weightedScore,
    ): array {
        return [
            'registration_id' => $registrationId,
            'request_id' => $requestId,
            'employee_id' => $employeeId,
            'dormitory_id' => $dormitoryId,
            'base_score' => $baseScore,
            'department_priority' => $departmentPriority,
            'weighted_score' => $weightedScore,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $eligible
     * @param  list<array<string, mixed>>  $excluded
     * @return array<string, mixed>
     */
    public static function buildPayload(
        array $eligible,
        array $excluded,
        string $lockedAtIso,
        int $programCapacity,
        string $randomSeed,
        ScoringConfig $scoringConfig,
    ): array {
        return [
            'semantic_contract_version' => self::PAYLOAD_VERSION,
            'lock_boundary' => [
                'locked_at' => $lockedAtIso,
                'program_capacity' => $programCapacity,
                'random_seed' => $randomSeed,
                'scoring_config_version' => $scoringConfig->version,
            ],
            'eligible' => $eligible,
            'excluded' => $excluded,
        ];
    }

    public static function assertProgramAligned(LotteryProgram $program, EligibleSnapshot $snapshot): void
    {
        if ($program->randomSeed !== $snapshot->randomSeed) {
            throw new LotteryValidationException('Locked program random seed does not match eligible snapshot.');
        }

        if ($program->scoringConfigVersion !== $snapshot->scoringConfig->version) {
            throw new LotteryValidationException('Locked program scoring config version does not match eligible snapshot.');
        }
    }

    /**
     * @param  list<array<string, mixed>>  $eligible
     */
    public static function assertScoresReproducible(
        array $eligible,
        EligibleSnapshot $snapshot,
        LotteryScoringEngine $engine,
    ): void {
        foreach ($eligible as $row) {
            self::assertEligibleRowShape($row);

            $expected = $engine->computeWeightedScore(
                config: $snapshot->scoringConfig,
                randomSeed: $snapshot->randomSeed,
                registrationId: (string) $row['registration_id'],
                baseScore: (float) $row['base_score'],
                departmentPriority: (int) $row['department_priority'],
            );

            if (abs($expected - (float) $row['weighted_score']) >= self::SCORE_EPSILON) {
                throw new LotteryValidationException(
                    'Frozen eligible row weighted_score is not reproducible from locked scoring inputs.',
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function assertEligibleRowShape(array $row): void
    {
        foreach (
            [
                'registration_id',
                'request_id',
                'employee_id',
                'dormitory_id',
                'base_score',
                'department_priority',
                'weighted_score',
            ] as $key
        ) {
            if (! array_key_exists($key, $row)) {
                throw new LotteryValidationException("Locked eligible row missing required key [{$key}].");
            }
        }
    }

    /**
     * @return list<array{registration_id: string, employee_id: string, weighted_score: float}>
     */
    public static function drawEligibleRows(EligibleSnapshot $snapshot): array
    {
        /** @var list<array<string, mixed>> $eligible */
        $eligible = $snapshot->payload['eligible'] ?? [];

        return array_map(
            static function (array $row): array {
                self::assertEligibleRowShape($row);

                return [
                    'registration_id' => (string) $row['registration_id'],
                    'employee_id' => (string) $row['employee_id'],
                    'weighted_score' => (float) $row['weighted_score'],
                ];
            },
            $eligible,
        );
    }
}
