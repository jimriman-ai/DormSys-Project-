<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Persistence\Models;

use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Employee\Infrastructure\Persistence\Models\EmployeeModel;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\States\RequestState;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\HasStates;

/**
 * @property string $id
 * @property string $code
 * @property string $employee_id
 * @property string|null $assigned_stage1_approver_identity_id
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
        'assigned_stage1_approver_identity_id',
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
     * Value-ref (AP-04): dormitory_id → dormitories.id — Eloquent only, no physical FK.
     *
     * @return BelongsTo<DormitoryModel, $this>
     */
    public function dormitory(): BelongsTo
    {
        return $this->belongsTo(DormitoryModel::class, 'dormitory_id');
    }

    /**
     * Value-ref (AP-04): assigned_stage1_approver_identity_id → identity_users.id —
     * Eloquent only; physical FK added then dropped (map: requests [EVOLVED] 2026_07_20 drop).
     *
     * @return BelongsTo<UserModel, $this>
     */
    public function assignedStage1Approver(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'assigned_stage1_approver_identity_id');
    }
}
