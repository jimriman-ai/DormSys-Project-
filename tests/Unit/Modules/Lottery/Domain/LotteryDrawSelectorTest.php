<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Domain;

use App\Modules\Lottery\Domain\Enums\LotteryResultOutcome;
use App\Modules\Lottery\Domain\Services\LotteryDrawSelector;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryDrawSelectorTest extends TestCase
{
    #[Test]
    public function it_selects_winners_up_to_capacity_and_ranks_reserves(): void
    {
        $selector = new LotteryDrawSelector;

        $eligible = [
            ['registration_id' => 'reg-low', 'employee_id' => 'emp-1', 'weighted_score' => 1.0],
            ['registration_id' => 'reg-high', 'employee_id' => 'emp-2', 'weighted_score' => 9.0],
            ['registration_id' => 'reg-mid', 'employee_id' => 'emp-3', 'weighted_score' => 5.0],
        ];

        $selections = $selector->select(2, $eligible);

        self::assertCount(3, $selections);
        self::assertSame('reg-high', $selections[0]['registration_id']);
        self::assertSame(LotteryResultOutcome::Winner, $selections[0]['outcome']);
        self::assertSame(1, $selections[0]['rank']);
        self::assertSame('reg-mid', $selections[1]['registration_id']);
        self::assertSame(LotteryResultOutcome::Winner, $selections[1]['outcome']);
        self::assertSame('reg-low', $selections[2]['registration_id']);
        self::assertSame(LotteryResultOutcome::Reserve, $selections[2]['outcome']);
    }

    #[Test]
    public function it_marks_all_eligible_as_winners_when_capacity_exceeds_count(): void
    {
        $selector = new LotteryDrawSelector;

        $eligible = [
            ['registration_id' => 'reg-a', 'employee_id' => 'emp-1', 'weighted_score' => 2.0],
            ['registration_id' => 'reg-b', 'employee_id' => 'emp-2', 'weighted_score' => 1.0],
        ];

        $selections = $selector->select(10, $eligible);

        self::assertCount(2, $selections);
        self::assertSame(LotteryResultOutcome::Winner, $selections[0]['outcome']);
        self::assertSame(LotteryResultOutcome::Winner, $selections[1]['outcome']);
    }
}
