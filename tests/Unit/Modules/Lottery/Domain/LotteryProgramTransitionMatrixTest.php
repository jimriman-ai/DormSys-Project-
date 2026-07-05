<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Domain;

use App\Modules\Lottery\Domain\States\ApprovedState;
use App\Modules\Lottery\Domain\States\CancelledState;
use App\Modules\Lottery\Domain\States\CompletedState;
use App\Modules\Lottery\Domain\States\DraftState;
use App\Modules\Lottery\Domain\States\DrawnState;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\States\RegistrationClosedState;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Modules\Lottery\Domain\States\WaitingApprovalState;
use App\Modules\Lottery\Infrastructure\Persistence\Models\LotteryProgramModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryProgramTransitionMatrixTest extends TestCase
{
    #[Test]
    #[DataProvider('allowedTransitionProvider')]
    public function it_allows_normative_lottery_program_transitions(string $from, string $to): void
    {
        $model = $this->persistProgramInState($from);

        expect($model->status->canTransitionTo($this->resolveStateClass($to)))->toBeTrue();
    }

    #[Test]
    #[DataProvider('forbiddenTransitionProvider')]
    public function it_blocks_non_normative_lottery_program_transitions(string $from, string $to): void
    {
        $model = $this->persistProgramInState($from);

        expect($model->status->canTransitionTo($this->resolveStateClass($to)))->toBeFalse();
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function allowedTransitionProvider(): array
    {
        return [
            'draft to waiting approval' => [DraftState::$name, WaitingApprovalState::$name],
            'draft to registration open' => [DraftState::$name, RegistrationOpenState::$name],
            'draft to cancelled' => [DraftState::$name, CancelledState::$name],
            'waiting approval to approved' => [WaitingApprovalState::$name, ApprovedState::$name],
            'approved to registration open' => [ApprovedState::$name, RegistrationOpenState::$name],
            'registration open to registration closed' => [RegistrationOpenState::$name, RegistrationClosedState::$name],
            'registration closed to locked' => [RegistrationClosedState::$name, LockedState::$name],
            'locked to drawn' => [LockedState::$name, DrawnState::$name],
            'drawn to completed' => [DrawnState::$name, CompletedState::$name],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function forbiddenTransitionProvider(): array
    {
        return [
            'draft to locked' => [DraftState::$name, LockedState::$name],
            'registration open to drawn' => [RegistrationOpenState::$name, DrawnState::$name],
            'completed to draft' => [CompletedState::$name, DraftState::$name],
            'cancelled to registration open' => [CancelledState::$name, RegistrationOpenState::$name],
            'locked to registration closed' => [LockedState::$name, RegistrationClosedState::$name],
        ];
    }

    private function persistProgramInState(string $status): LotteryProgramModel
    {
        $model = new LotteryProgramModel;
        $model->forceFill([
            'id' => UuidGenerator::uuid7(),
            'title' => 'Transition Test Program',
            'dormitory_id' => UuidGenerator::uuid7(),
            'capacity' => 10,
            'registration_starts_at' => '2026-07-01 00:00:00',
            'registration_ends_at' => '2026-07-15 23:59:59',
            'status' => $status,
        ]);
        $model->syncOriginal();

        return $model;
    }

    /**
     * @return class-string
     */
    private function resolveStateClass(string $status): string
    {
        return match ($status) {
            DraftState::$name => DraftState::class,
            WaitingApprovalState::$name => WaitingApprovalState::class,
            ApprovedState::$name => ApprovedState::class,
            RegistrationOpenState::$name => RegistrationOpenState::class,
            RegistrationClosedState::$name => RegistrationClosedState::class,
            LockedState::$name => LockedState::class,
            DrawnState::$name => DrawnState::class,
            CompletedState::$name => CompletedState::class,
            CancelledState::$name => CancelledState::class,
            default => throw new \InvalidArgumentException('Unknown state: '.$status),
        };
    }
}
