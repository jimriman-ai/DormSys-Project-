<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Services\CancelLotteryProgramAction;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Lottery\Domain\Events\LotteryProgramCreated;
use App\Modules\Lottery\Domain\Events\LotteryProgramStateChanged;
use App\Modules\Lottery\Domain\Exceptions\InvalidLotteryTransitionException;
use App\Modules\Lottery\Domain\States\CancelledState;
use App\Modules\Lottery\Domain\States\DraftState;
use App\Modules\Lottery\Domain\States\RegistrationClosedState;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('creates a draft program and opens registration', function (): void {
    Event::fake([LotteryProgramCreated::class, LotteryProgramStateChanged::class]);

    $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

    $draft = runLotteryMutation(fn () => app(CreateLotteryProgramAction::class)->execute(
        title: 'Summer 2026 Lottery',
        dormitoryId: $dormitoryId,
        capacity: 30,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    ));

    expect($draft->isDraft())->toBeTrue();
    expect($draft->status)->toBe(DraftState::$name);

    Event::assertDispatched(LotteryProgramCreated::class);

    $opened = runLotteryMutation(fn () => app(OpenRegistrationAction::class)->execute($draft->requireId()));

    expect($opened->status)->toBe(RegistrationOpenState::$name);
    expect($opened->canAcceptEnrollment())->toBeTrue();

    Event::assertDispatched(LotteryProgramStateChanged::class, function (LotteryProgramStateChanged $event) use ($draft): bool {
        return $event->aggregateId === $draft->requireId()->value
            && ($event->payload['previous_status'] ?? null) === DraftState::$name
            && ($event->payload['new_status'] ?? null) === RegistrationOpenState::$name;
    });
});

it('closes registration after opening', function (): void {
    $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

    $draft = runLotteryMutation(fn () => app(CreateLotteryProgramAction::class)->execute(
        title: 'Close Test Lottery',
        dormitoryId: $dormitoryId,
        capacity: 15,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    ));

    $opened = runLotteryMutation(fn () => app(OpenRegistrationAction::class)->execute($draft->requireId()));
    $closed = runLotteryMutation(fn () => app(CloseRegistrationAction::class)->execute($opened->requireId()));

    expect($closed->status)->toBe(RegistrationClosedState::$name);
    expect($closed->canLock())->toBeTrue();
});

it('cancels an open program with reason', function (): void {
    $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

    $draft = runLotteryMutation(fn () => app(CreateLotteryProgramAction::class)->execute(
        title: 'Cancel Test Lottery',
        dormitoryId: $dormitoryId,
        capacity: 10,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    ));

    $opened = runLotteryMutation(fn () => app(OpenRegistrationAction::class)->execute($draft->requireId()));
    $cancelled = runLotteryMutation(fn () => app(CancelLotteryProgramAction::class)->execute(
        $opened->requireId(),
        'Insufficient dormitory capacity',
    ));

    expect($cancelled->status)->toBe(CancelledState::$name);
    expect($cancelled->cancelledReason)->toBe('Insufficient dormitory capacity');
    expect($cancelled->isTerminal())->toBeTrue();
});

it('rejects closing registration when not open', function (): void {
    $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

    $draft = runLotteryMutation(fn () => app(CreateLotteryProgramAction::class)->execute(
        title: 'Invalid Close Lottery',
        dormitoryId: $dormitoryId,
        capacity: 10,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    ));

    runLotteryMutation(fn () => app(CloseRegistrationAction::class)->execute($draft->requireId()));
})->throws(InvalidLotteryTransitionException::class);
