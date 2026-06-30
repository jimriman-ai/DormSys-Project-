<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Domain;

use App\Modules\Lottery\Domain\Enums\LotteryProgramStatus;
use App\Modules\Lottery\Domain\Enums\LotteryResultOutcome;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryRegistrationId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryResultId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\Exceptions\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryValueObjectsTest extends TestCase
{
    #[Test]
    public function it_accepts_valid_uuid_value_objects(): void
    {
        $uuid = UuidGenerator::uuid7();

        expect((string) LotteryProgramId::fromString($uuid))->toBe($uuid);
        expect((string) LotteryRegistrationId::fromString($uuid))->toBe($uuid);
        expect((string) LotteryResultId::fromString($uuid))->toBe($uuid);
        expect((string) DormitorySiteId::fromString($uuid))->toBe($uuid);
        expect((string) EmployeeReferenceId::fromString($uuid))->toBe($uuid);
        expect((string) RequestReferenceId::fromString($uuid))->toBe($uuid);
    }

    #[Test]
    public function it_rejects_invalid_uuid_value_objects(): void
    {
        $this->expectException(ValidationException::class);

        LotteryProgramId::fromString('not-a-uuid');
    }

    #[Test]
    public function it_exposes_lottery_program_status_terminal_semantics(): void
    {
        expect(LotteryProgramStatus::Completed->isTerminal())->toBeTrue();
        expect(LotteryProgramStatus::Cancelled->isTerminal())->toBeTrue();
        expect(LotteryProgramStatus::Draft->isTerminal())->toBeFalse();
        expect(LotteryProgramStatus::RegistrationOpen->allowsEnrollment())->toBeTrue();
    }

    #[Test]
    public function it_exposes_lottery_result_outcome_values(): void
    {
        expect(LotteryResultOutcome::Winner->value)->toBe('winner');
        expect(LotteryResultOutcome::Reserve->value)->toBe('reserve');
    }
}
