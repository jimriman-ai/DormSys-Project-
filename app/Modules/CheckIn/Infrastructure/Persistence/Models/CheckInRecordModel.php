<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Infrastructure\Persistence\Models;

use App\Support\Models\BaseModel;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $allocation_id
 * @property Carbon $checked_in_at
 * @property Carbon|null $checked_out_at
 * @property string $operator_id
 */
class CheckInRecordModel extends BaseModel
{
    protected $table = 'check_in_records';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'allocation_id',
        'checked_in_at',
        'checked_out_at',
        'operator_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
        ]);
    }
}
