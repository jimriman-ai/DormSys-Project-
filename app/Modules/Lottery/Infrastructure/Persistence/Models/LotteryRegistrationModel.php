<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Persistence\Models;

use App\Support\Models\BaseModel;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $program_id
 * @property string $request_id
 * @property string $employee_id
 * @property string|null $weighted_score
 * @property Carbon $enrolled_at
 */
class LotteryRegistrationModel extends BaseModel
{
    protected $table = 'lottery_registrations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'program_id',
        'request_id',
        'employee_id',
        'weighted_score',
        'enrolled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'weighted_score' => 'decimal:8',
            'enrolled_at' => 'datetime',
        ]);
    }
}
