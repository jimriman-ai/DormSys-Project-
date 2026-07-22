<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Persistence\Models;

use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Employee\Infrastructure\Persistence\Models\EmployeeModel;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
use App\Modules\Voucher\Domain\Enums\TriggerIntakeStatus;
use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $id
 * @property string $correlation_id
 * @property string $employee_id
 * @property string|null $dormitory_id
 * @property string|null $request_id
 * @property string $stay_period
 * @property TriggerSource $source
 * @property TriggerIntakeStatus $status
 * @property Carbon|null $issuance_path_completed_at
 * @property string|null $superseded_by_trigger_id
 * @property array<string, mixed> $upstream_facts
 */
class VoucherIssuanceTriggerModel extends BaseModel
{
    protected $table = 'voucher_issuance_triggers';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'correlation_id',
        'employee_id',
        'dormitory_id',
        'request_id',
        'stay_period',
        'source',
        'status',
        'issuance_path_completed_at',
        'superseded_by_trigger_id',
        'upstream_facts',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'source' => TriggerSource::class,
            'status' => TriggerIntakeStatus::class,
            'issuance_path_completed_at' => 'datetime',
            'upstream_facts' => 'array',
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
     * Intra-voucher value-ref: superseded_by_trigger_id → voucher_issuance_triggers.id.
     *
     * @return BelongsTo<VoucherIssuanceTriggerModel, $this>
     */
    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'superseded_by_trigger_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('voucher_trigger')
            ->logOnly([
                'correlation_id',
                'employee_id',
                'source',
                'status',
                'issuance_path_completed_at',
                'superseded_by_trigger_id',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'voucher trigger accepted',
                'updated' => 'voucher trigger updated',
                default => $eventName,
            });
    }
}
