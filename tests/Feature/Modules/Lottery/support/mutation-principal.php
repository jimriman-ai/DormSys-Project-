<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function runLotteryMutation(callable $callback, ?string $actorId = null): mixed
{
    return withLotteryMutationActor($callback, $actorId);
}

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function runLotterySystemMutation(callable $callback): mixed
{
    return asLotterySystemMutation($callback);
}

function createLotteryProgramForTest(
    string $title,
    string $dormitoryId,
    int $capacity,
    DateTimeImmutable $registrationStartsAt,
    DateTimeImmutable $registrationEndsAt,
): LotteryProgram {
    return runLotteryMutation(fn () => app(CreateLotteryProgramAction::class)->execute(
        title: $title,
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: $capacity,
        registrationStartsAt: $registrationStartsAt,
        registrationEndsAt: $registrationEndsAt,
    ));
}

function openLotteryProgramForTest(LotteryProgramId $programId): LotteryProgram
{
    return runLotteryMutation(fn () => app(OpenRegistrationAction::class)->execute($programId));
}
