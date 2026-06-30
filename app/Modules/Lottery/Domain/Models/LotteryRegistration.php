<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\Models;

use App\Modules\Lottery\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryRegistrationId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use DateTimeImmutable;

final class LotteryRegistration
{
    public function __construct(
        public readonly ?LotteryRegistrationId $id,
        public readonly LotteryProgramId $programId,
        public readonly RequestReferenceId $requestId,
        public readonly EmployeeReferenceId $employeeId,
        public readonly DateTimeImmutable $enrolledAt,
        public ?float $weightedScore = null,
    ) {}

    public static function enroll(
        LotteryProgramId $programId,
        RequestReferenceId $requestId,
        EmployeeReferenceId $employeeId,
        DateTimeImmutable $enrolledAt,
    ): self {
        return new self(
            id: null,
            programId: $programId,
            requestId: $requestId,
            employeeId: $employeeId,
            enrolledAt: $enrolledAt,
        );
    }

    public function requireId(): LotteryRegistrationId
    {
        if ($this->id === null) {
            throw new \LogicException('Lottery registration identifier is not assigned.');
        }

        return $this->id;
    }

    public function withWeightedScore(float $weightedScore): self
    {
        return new self(
            id: $this->id,
            programId: $this->programId,
            requestId: $this->requestId,
            employeeId: $this->employeeId,
            enrolledAt: $this->enrolledAt,
            weightedScore: $weightedScore,
        );
    }
}
