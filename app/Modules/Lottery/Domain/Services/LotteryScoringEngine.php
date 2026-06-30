<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\Services;

use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;

final class LotteryScoringEngine
{
    public function computeWeightedScore(
        ScoringConfig $config,
        string $randomSeed,
        string $registrationId,
        float $baseScore,
        int $departmentPriority,
    ): float {
        $normalizedWeight = (
            ($baseScore * $config->baseScoreCoefficient)
            + ($departmentPriority * $config->departmentPriorityCoefficient)
        ) / $config->normalizationDivisor;

        $prngFactor = $this->prngFactor($randomSeed, $registrationId) * $config->prngScale;

        return $normalizedWeight + $prngFactor;
    }

    public function prngFactor(string $randomSeed, string $registrationId): float
    {
        $hash = hash('sha256', $randomSeed.'|'.$registrationId);
        $unsigned = hexdec(substr($hash, 0, 8));

        return $unsigned / 0xFFFFFFFF;
    }
}
