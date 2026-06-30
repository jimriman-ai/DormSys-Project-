<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Domain;

use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\States\ApprovedState;
use App\Modules\Lottery\Domain\States\CancelledState;
use App\Modules\Lottery\Domain\States\DraftState;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\States\RegistrationClosedState;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryProgramLifecycleTest extends TestCase
{
    #[Test]
    public function it_opens_registration_from_draft_or_approved(): void
    {
        $draft = $this->sampleProgram(DraftState::$name);
        expect($draft->canOpenRegistration())->toBeTrue();
        expect($draft->markRegistrationOpen()->status)->toBe(RegistrationOpenState::$name);

        $approved = $this->sampleProgram(ApprovedState::$name);
        expect($approved->canOpenRegistration())->toBeTrue();
        expect($approved->markRegistrationOpen()->status)->toBe(RegistrationOpenState::$name);
    }

    #[Test]
    public function it_closes_registration_only_from_open_state(): void
    {
        $open = $this->sampleProgram(RegistrationOpenState::$name);
        expect($open->canCloseRegistration())->toBeTrue();
        expect($open->markRegistrationClosed()->status)->toBe(RegistrationClosedState::$name);

        $draft = $this->sampleProgram(DraftState::$name);
        expect($draft->canCloseRegistration())->toBeFalse();
    }

    #[Test]
    public function it_cancels_with_reason_in_allowed_states(): void
    {
        $open = $this->sampleProgram(RegistrationOpenState::$name);
        $cancelled = $open->markCancelled('Operator cancelled draw');

        expect($cancelled->status)->toBe(CancelledState::$name);
        expect($cancelled->cancelledReason)->toBe('Operator cancelled draw');
        expect($cancelled->isTerminal())->toBeTrue();
    }

    #[Test]
    #[DataProvider('nonCancellableStatesProvider')]
    public function it_does_not_allow_cancellation_from_locked_or_terminal_states(string $status): void
    {
        $program = $this->sampleProgram($status);

        expect($program->isCancellable())->toBeFalse();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function nonCancellableStatesProvider(): array
    {
        return [
            'locked' => [LockedState::$name],
            'cancelled' => [CancelledState::$name],
        ];
    }

    private function sampleProgram(string $status): LotteryProgram
    {
        return new LotteryProgram(
            id: LotteryProgramId::fromString(UuidGenerator::uuid7()),
            title: 'Lifecycle Program',
            dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
            capacity: 25,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
            status: $status,
        );
    }
}
