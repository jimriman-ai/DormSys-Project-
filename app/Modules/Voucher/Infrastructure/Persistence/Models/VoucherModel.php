<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Persistence\Models;

use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Employee\Infrastructure\Persistence\Models\EmployeeModel;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $id
 * @property string $eligibility_outcome_id
 * @property string $trigger_id
 * @property string $correlation_id
 * @property string $employee_id
 * @property string|null $dormitory_id
 * @property string|null $request_id
 * @property TriggerSource $upstream_source
 * @property string $code
 * @property VoucherLifecycleState $lifecycle_state
 * @property string $stay_period
 * @property Carbon $validity_start
 * @property Carbon $validity_end
 * @property Carbon $issued_at
 * @property Carbon|null $archived_at
 */
class VoucherModel extends BaseModel
{
    protected $table = 'vouchers';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'eligibility_outcome_id',
        'trigger_id',
        'correlation_id',
        'employee_id',
        'dormitory_id',
        'request_id',
        'upstream_source',
        'code',
        'lifecycle_state',
        'stay_period',
        'validity_start',
        'validity_end',
        'issued_at',
        'archived_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'upstream_source' => TriggerSource::class,
            'lifecycle_state' => VoucherLifecycleState::class,
            'validity_start' => 'datetime',
            'validity_end' => 'datetime',
            'issued_at' => 'datetime',
            'archived_at' => 'datetime',
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

    /**
     * Intra-voucher value-ref: eligibility_outcome_id → voucher_eligibility_outcomes.id.
     *
     * @return BelongsTo<VoucherEligibilityOutcomeModel, $this>
     */
    public function eligibilityOutcome(): BelongsTo
    {
        return $this->belongsTo(VoucherEligibilityOutcomeModel::class, 'eligibility_outcome_id');
    }

    /**
     * Intra-voucher inverse: voucher_lifecycle_transitions.voucher_id.
     *
     * @return HasMany<VoucherLifecycleTransitionModel, $this>
     */
    public function lifecycleTransitions(): HasMany
    {
        return $this->hasMany(VoucherLifecycleTransitionModel::class, 'voucher_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('voucher')
            ->logOnly([
                'code',
                'lifecycle_state',
                'employee_id',
                'correlation_id',
                'archived_at',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'voucher issued',
                'updated' => 'voucher updated',
                default => $eventName,
            });
    }
}
