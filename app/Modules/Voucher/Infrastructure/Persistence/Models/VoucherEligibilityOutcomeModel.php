<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Persistence\Models;

use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Employee\Infrastructure\Persistence\Models\EmployeeModel;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
use App\Modules\Voucher\Domain\Enums\EligibilityOutcome;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $id
 * @property string $trigger_id
 * @property string $correlation_id
 * @property string $employee_id
 * @property string|null $dormitory_id
 * @property string|null $request_id
 * @property EligibilityOutcome $outcome
 * @property list<string> $reason_codes
 * @property string $rationale
 * @property Carbon $evaluated_at
 */
class VoucherEligibilityOutcomeModel extends BaseModel
{
    protected $table = 'voucher_eligibility_outcomes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'trigger_id',
        'correlation_id',
        'employee_id',
        'dormitory_id',
        'request_id',
        'outcome',
        'reason_codes',
        'rationale',
        'evaluated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'outcome' => EligibilityOutcome::class,
            'reason_codes' => 'array',
            'evaluated_at' => 'datetime',
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
     * Value-ref (AP-04): request_id → requests.id — Eloquent only, no physical FK.
     *
     * @return BelongsTo<RequestModel, $this>
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(RequestModel::class, 'request_id');
    }

    /**
     * Intra-voucher value-ref: trigger_id → voucher_issuance_triggers.id.
     *
     * @return BelongsTo<VoucherIssuanceTriggerModel, $this>
     */
    public function trigger(): BelongsTo
    {
        return $this->belongsTo(VoucherIssuanceTriggerModel::class, 'trigger_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('voucher_eligibility')
            ->logOnly([
                'trigger_id',
                'correlation_id',
                'employee_id',
                'outcome',
                'reason_codes',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'voucher eligibility evaluated',
                'updated' => 'voucher eligibility updated',
                default => $eventName,
            });
    }
}
