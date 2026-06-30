<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\ValueObjects;

use App\Modules\Lottery\Domain\Exceptions\LotteryValidationException;

final readonly class ScoringConfig
{
    public function __construct(
        public string $version,
        public float $baseScoreCoefficient,
        public float $departmentPriorityCoefficient,
        public float $normalizationDivisor,
        public float $prngScale,
    ) {
        if ($this->normalizationDivisor <= 0.0) {
            throw new LotteryValidationException('Scoring normalization divisor must be greater than zero.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        foreach (['version', 'base_score_coefficient', 'department_priority_coefficient', 'normalization_divisor', 'prng_scale'] as $key) {
            if (! array_key_exists($key, $data)) {
                throw new LotteryValidationException("Scoring config missing required key [{$key}].");
            }
        }

        return new self(
            version: (string) $data['version'],
            baseScoreCoefficient: (float) $data['base_score_coefficient'],
            departmentPriorityCoefficient: (float) $data['department_priority_coefficient'],
            normalizationDivisor: (float) $data['normalization_divisor'],
            prngScale: (float) $data['prng_scale'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'base_score_coefficient' => $this->baseScoreCoefficient,
            'department_priority_coefficient' => $this->departmentPriorityCoefficient,
            'normalization_divisor' => $this->normalizationDivisor,
            'prng_scale' => $this->prngScale,
        ];
    }
}
