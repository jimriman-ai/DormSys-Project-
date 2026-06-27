<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Persistence\Models;

use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\States\RequestState;
use App\Support\Models\BaseModel;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\HasStates;

/**
 * @property string $id
 * @property string $code
 * @property string $employee_id
 * @property string $dormitory_id
 * @property RequestType $type
 * @property Carbon $check_in_date
 * @property Carbon $check_out_date
 * @property RequestState $status
 * @property Carbon|null $submitted_at
 * @property Carbon|null $cancelled_at
 * @property string|null $rejection_reason
 */
class RequestModel extends BaseModel
{
    use HasStates;

    protected $table = 'requests';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'employee_id',
        'dormitory_id',
        'type',
        'check_in_date',
        'check_out_date',
        'status',
        'submitted_at',
        'cancelled_at',
        'rejection_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'type' => RequestType::class,
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'status' => RequestState::class,
            'submitted_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ]);
    }
}
