<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use DateTimeImmutable;

interface LotteryProgramRepositoryContract
{
    public function save(LotteryProgram $program): LotteryProgram;

    public function findById(LotteryProgramId $id): ?LotteryProgram;

    public function findByIdForUpdate(LotteryProgramId $id): ?LotteryProgram;

    /**
     * Programs past registration end still accepting close/lock automation.
     *
     * @return list<LotteryProgram>
     */
    public function findPastRegistrationEndEligibleForAutoLock(DateTimeImmutable $asOf): array;

    /**
     * Locked programs without persisted draw results.
     *
     * @return list<LotteryProgram>
     */
    public function findLockedReadyForDraw(): array;
}
