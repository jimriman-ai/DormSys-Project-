<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\Services;

use App\Modules\Lottery\Domain\Enums\LotteryResultOutcome;

final class LotteryDrawSelector
{
    /**
     * @param  list<array{registration_id: string, employee_id: string, weighted_score: float}>  $eligible
     * @return list<array{registration_id: string, employee_id: string, rank: int, outcome: LotteryResultOutcome, weighted_score: float}>
     */
    public function select(int $capacity, array $eligible): array
    {
        if ($capacity <= 0) {
            return [];
        }

        $sorted = $eligible;
        usort(
            $sorted,
            static function (array $left, array $right): int {
                $scoreCompare = ($right['weighted_score'] ?? 0.0) <=> ($left['weighted_score'] ?? 0.0);

                if ($scoreCompare !== 0) {
                    return $scoreCompare;
                }

                return strcmp((string) $left['registration_id'], (string) $right['registration_id']);
            },
        );

        $selections = [];
        $rank = 1;

        foreach ($sorted as $index => $row) {
            $selections[] = [
                'registration_id' => (string) $row['registration_id'],
                'employee_id' => (string) $row['employee_id'],
                'rank' => $rank,
                'outcome' => $index < $capacity
                    ? LotteryResultOutcome::Winner
                    : LotteryResultOutcome::Reserve,
                'weighted_score' => (float) $row['weighted_score'],
            ];
            $rank++;
        }

        return $selections;
    }
}
