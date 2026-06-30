<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\Models;

use App\Modules\Lottery\Domain\States\ApprovedState;
use App\Modules\Lottery\Domain\States\CancelledState;
use App\Modules\Lottery\Domain\States\CompletedState;
use App\Modules\Lottery\Domain\States\DraftState;
use App\Modules\Lottery\Domain\States\DrawnState;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\States\RegistrationClosedState;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Modules\Lottery\Domain\States\WaitingApprovalState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use DateTimeImmutable;

final class LotteryProgram
{
    public function __construct(
        public readonly ?LotteryProgramId $id,
        public readonly string $title,
        public readonly DormitorySiteId $dormitoryId,
        public readonly int $capacity,
        public readonly DateTimeImmutable $registrationStartsAt,
        public readonly DateTimeImmutable $registrationEndsAt,
        public string $status,
        public ?string $randomSeed = null,
        public ?string $scoringConfigVersion = null,
        public ?string $cancelledReason = null,
        public ?DateTimeImmutable $lockedAt = null,
        public ?DateTimeImmutable $drawnAt = null,
    ) {}

    public static function createDraft(
        string $title,
        DormitorySiteId $dormitoryId,
        int $capacity,
        DateTimeImmutable $registrationStartsAt,
        DateTimeImmutable $registrationEndsAt,
    ): self {
        return new self(
            id: null,
            title: $title,
            dormitoryId: $dormitoryId,
            capacity: $capacity,
            registrationStartsAt: $registrationStartsAt,
            registrationEndsAt: $registrationEndsAt,
            status: DraftState::$name,
        );
    }

    public function requireId(): LotteryProgramId
    {
        if ($this->id === null) {
            throw new \LogicException('Lottery program identifier is not assigned.');
        }

        return $this->id;
    }

    public function isApproved(): bool
    {
        return $this->status === ApprovedState::$name;
    }

    public function isRegistrationClosed(): bool
    {
        return $this->status === RegistrationClosedState::$name;
    }

    public function isDraft(): bool
    {
        return $this->status === DraftState::$name;
    }

    public function isRegistrationOpen(): bool
    {
        return $this->status === RegistrationOpenState::$name;
    }

    public function isCompleted(): bool
    {
        return $this->status === CompletedState::$name;
    }

    public function isDrawn(): bool
    {
        return $this->status === DrawnState::$name;
    }

    public function isLocked(): bool
    {
        return $this->status === LockedState::$name;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [
            DraftState::$name,
            WaitingApprovalState::$name,
            ApprovedState::$name,
            RegistrationOpenState::$name,
            RegistrationClosedState::$name,
        ], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            CompletedState::$name,
            CancelledState::$name,
        ], true);
    }

    public function canAcceptEnrollment(): bool
    {
        return $this->status === RegistrationOpenState::$name;
    }

    public function canLock(): bool
    {
        return $this->status === RegistrationClosedState::$name;
    }

    public function canDraw(): bool
    {
        return $this->status === LockedState::$name;
    }

    public function canComplete(): bool
    {
        return $this->status === DrawnState::$name;
    }

    public function canOpenRegistration(): bool
    {
        return $this->isDraft() || $this->isApproved();
    }

    public function canCloseRegistration(): bool
    {
        return $this->isRegistrationOpen();
    }

    public function markRegistrationOpen(): self
    {
        return $this->withStatus(RegistrationOpenState::$name);
    }

    public function markRegistrationClosed(): self
    {
        return $this->withStatus(RegistrationClosedState::$name);
    }

    public function markCancelled(string $reason): self
    {
        return $this->withStatus(CancelledState::$name, $reason);
    }

    public function markLocked(
        string $randomSeed,
        string $scoringConfigVersion,
        DateTimeImmutable $lockedAt,
    ): self {
        return new self(
            id: $this->id,
            title: $this->title,
            dormitoryId: $this->dormitoryId,
            capacity: $this->capacity,
            registrationStartsAt: $this->registrationStartsAt,
            registrationEndsAt: $this->registrationEndsAt,
            status: LockedState::$name,
            randomSeed: $randomSeed,
            scoringConfigVersion: $scoringConfigVersion,
            cancelledReason: $this->cancelledReason,
            lockedAt: $lockedAt,
            drawnAt: $this->drawnAt,
        );
    }

    public function markDrawn(DateTimeImmutable $drawnAt): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            dormitoryId: $this->dormitoryId,
            capacity: $this->capacity,
            registrationStartsAt: $this->registrationStartsAt,
            registrationEndsAt: $this->registrationEndsAt,
            status: DrawnState::$name,
            randomSeed: $this->randomSeed,
            scoringConfigVersion: $this->scoringConfigVersion,
            cancelledReason: $this->cancelledReason,
            lockedAt: $this->lockedAt,
            drawnAt: $drawnAt,
        );
    }

    public function markCompleted(): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            dormitoryId: $this->dormitoryId,
            capacity: $this->capacity,
            registrationStartsAt: $this->registrationStartsAt,
            registrationEndsAt: $this->registrationEndsAt,
            status: CompletedState::$name,
            randomSeed: $this->randomSeed,
            scoringConfigVersion: $this->scoringConfigVersion,
            cancelledReason: $this->cancelledReason,
            lockedAt: $this->lockedAt,
            drawnAt: $this->drawnAt,
        );
    }

    public function withStatus(string $status, ?string $cancelledReason = null): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            dormitoryId: $this->dormitoryId,
            capacity: $this->capacity,
            registrationStartsAt: $this->registrationStartsAt,
            registrationEndsAt: $this->registrationEndsAt,
            status: $status,
            randomSeed: $this->randomSeed,
            scoringConfigVersion: $this->scoringConfigVersion,
            cancelledReason: $cancelledReason ?? $this->cancelledReason,
            lockedAt: $this->lockedAt,
            drawnAt: $this->drawnAt,
        );
    }
}
