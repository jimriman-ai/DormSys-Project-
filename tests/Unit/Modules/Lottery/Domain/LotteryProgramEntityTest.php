<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Domain;

use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\States\CompletedState;
use App\Modules\Lottery\Domain\States\DraftState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryProgramEntityTest extends TestCase
{
    #[Test]
    public function it_creates_draft_program_with_terminal_semantics(): void
    {
        $program = LotteryProgram::createDraft(
            title: 'Summer 2026 Draw',
            dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
            capacity: 50,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
        );

        expect($program->isDraft())->toBeTrue();
        expect($program->status)->toBe(DraftState::$name);
        expect($program->isCancellable())->toBeTrue();
        expect($program->isTerminal())->toBeFalse();
        expect($program->canAcceptEnrollment())->toBeFalse();
    }

    #[Test]
    public function it_marks_completed_as_terminal(): void
    {
        $program = new LotteryProgram(
            id: LotteryProgramId::fromString(UuidGenerator::uuid7()),
            title: 'Done',
            dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
            capacity: 1,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
            status: CompletedState::$name,
        );

        expect($program->isTerminal())->toBeTrue();
        expect($program->isCancellable())->toBeFalse();
    }
}
