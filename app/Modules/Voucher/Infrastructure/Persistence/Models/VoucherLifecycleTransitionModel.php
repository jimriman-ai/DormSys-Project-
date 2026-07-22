<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Persistence\Models;

use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $id
 * @property string $voucher_id
 * @property VoucherLifecycleState|null $from_state
 * @property VoucherLifecycleState $to_state
 * @property string $correlation_id
 * @property Carbon $occurred_at
 * @property array<string, mixed> $payload
 */
class VoucherLifecycleTransitionModel extends BaseModel
{
    protected $table = 'voucher_lifecycle_transitions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'voucher_id',
        'from_state',
        'to_state',
        'correlation_id',
        'occurred_at',
        'payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'from_state' => VoucherLifecycleState::class,
            'to_state' => VoucherLifecycleState::class,
            'occurred_at' => 'datetime',
            'payload' => 'array',
        ]);
    }

    /**
     * Intra-voucher value-ref: voucher_id → vouchers.id.
     *
     * @return BelongsTo<VoucherModel, $this>
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(VoucherModel::class, 'voucher_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('voucher_transition')
            ->logOnly(['voucher_id', 'from_state', 'to_state', 'correlation_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'voucher lifecycle transition recorded',
                default => $eventName,
            });
    }
}
