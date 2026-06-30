<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Persistence\Models;

use App\Modules\Lottery\Domain\Enums\LotteryResultOutcome;
use App\Support\Models\BaseModel;

/**
 * @property string $id
 * @property string $program_id
 * @property string $registration_id
 * @property int $rank
 * @property LotteryResultOutcome $outcome
 */
class LotteryResultModel extends BaseModel
{
    protected $table = 'lottery_results';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'program_id',
        'registration_id',
        'rank',
        'outcome',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'outcome' => LotteryResultOutcome::class,
        ]);
    }
}
