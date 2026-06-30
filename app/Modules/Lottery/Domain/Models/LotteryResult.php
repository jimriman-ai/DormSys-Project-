<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\Models;

use App\Modules\Lottery\Domain\Enums\LotteryResultOutcome;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryRegistrationId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryResultId;

final class LotteryResult
{
    public function __construct(
        public readonly ?LotteryResultId $id,
        public readonly LotteryProgramId $programId,
        public readonly LotteryRegistrationId $registrationId,
        public readonly int $rank,
        public readonly LotteryResultOutcome $outcome,
    ) {}

    public static function create(
        LotteryProgramId $programId,
        LotteryRegistrationId $registrationId,
        int $rank,
        LotteryResultOutcome $outcome,
    ): self {
        return new self(
            id: null,
            programId: $programId,
            registrationId: $registrationId,
            rank: $rank,
            outcome: $outcome,
        );
    }

    public function requireId(): LotteryResultId
    {
        if ($this->id === null) {
            throw new \LogicException('Lottery result identifier is not assigned.');
        }

        return $this->id;
    }
}
