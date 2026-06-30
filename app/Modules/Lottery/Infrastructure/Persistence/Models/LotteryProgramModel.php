<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Persistence\Models;

use App\Modules\Lottery\Domain\States\LotteryProgramState;
use App\Support\Models\BaseModel;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\HasStates;

/**
 * @property string $id
 * @property string $title
 * @property string $dormitory_id
 * @property int $capacity
 * @property Carbon $registration_starts_at
 * @property Carbon $registration_ends_at
 * @property LotteryProgramState $status
 * @property string|null $random_seed
 * @property string|null $scoring_config_version
 * @property string|null $cancelled_reason
 * @property Carbon|null $locked_at
 * @property Carbon|null $drawn_at
 */
class LotteryProgramModel extends BaseModel
{
    use HasStates;

    protected $table = 'lottery_programs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'dormitory_id',
        'capacity',
        'registration_starts_at',
        'registration_ends_at',
        'status',
        'random_seed',
        'scoring_config_version',
        'cancelled_reason',
        'locked_at',
        'drawn_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'registration_starts_at' => 'datetime',
            'registration_ends_at' => 'datetime',
            'status' => LotteryProgramState::class,
            'locked_at' => 'datetime',
            'drawn_at' => 'datetime',
        ]);
    }
}
