<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\Models;

use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotterySnapshotId;
use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;

final class EligibleSnapshot
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly ?LotterySnapshotId $id,
        public readonly LotteryProgramId $programId,
        public readonly array $payload,
        public readonly string $randomSeed,
        public readonly ScoringConfig $scoringConfig,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function capture(
        LotteryProgramId $programId,
        array $payload,
        string $randomSeed,
        ScoringConfig $scoringConfig,
    ): self {
        return new self(
            id: null,
            programId: $programId,
            payload: $payload,
            randomSeed: $randomSeed,
            scoringConfig: $scoringConfig,
        );
    }

    public function requireId(): LotterySnapshotId
    {
        if ($this->id === null) {
            throw new \LogicException('Eligible snapshot identifier is not assigned.');
        }

        return $this->id;
    }
}
