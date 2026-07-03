<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Persistence\Models;

use App\Modules\Voucher\Domain\Enums\TriggerSource;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Support\Models\BaseModel;
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
