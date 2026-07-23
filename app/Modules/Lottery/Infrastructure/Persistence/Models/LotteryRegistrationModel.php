<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Persistence\Models;

use App\Modules\Employee\Infrastructure\Persistence\Models\EmployeeModel;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    /**
     * program_id → lottery_programs.id (physical FK present; Eloquent relation).
     *
     * @return BelongsTo<LotteryProgramModel, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(LotteryProgramModel::class, 'program_id');
    }

    /**
     * Value-ref (AP-04): employee_id → employee_employees.id — Eloquent only, no physical FK.
     *
     * @return BelongsTo<EmployeeModel, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id');
    }

    /**
     * Value-ref (AP-04): request_id → requests.id — Eloquent only, no physical FK.
     *
     * @return BelongsTo<RequestModel, $this>
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(RequestModel::class, 'request_id');
    }
}
