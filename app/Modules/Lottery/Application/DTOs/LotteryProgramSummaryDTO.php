<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\DTOs;

use App\Modules\Lottery\Domain\Models\LotteryProgram;

final readonly class LotteryProgramSummaryDTO
{
    public function __construct(
        public string $id,
        public string $title,
        public string $dormitoryId,
        public int $capacity,
        public string $registrationStartsAt,
        public string $registrationEndsAt,
        public string $status,
        public ?string $randomSeed,
        public ?string $scoringConfigVersion,
        public ?string $cancelledReason,
        public ?string $lockedAt,
        public ?string $drawnAt,
    ) {}

    public static function fromProgram(LotteryProgram $program): self
    {
        return new self(
            id: $program->requireId()->value,
            title: $program->title,
            dormitoryId: $program->dormitoryId->value,
            capacity: $program->capacity,
            registrationStartsAt: $program->registrationStartsAt->format(DATE_ATOM),
            registrationEndsAt: $program->registrationEndsAt->format(DATE_ATOM),
            status: $program->status,
            randomSeed: $program->randomSeed,
            scoringConfigVersion: $program->scoringConfigVersion,
            cancelledReason: $program->cancelledReason,
            lockedAt: $program->lockedAt?->format(DATE_ATOM),
            drawnAt: $program->drawnAt?->format(DATE_ATOM),
        );
    }
}
