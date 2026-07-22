<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Infrastructure\Persistence\Models;

use App\Modules\Allocation\Infrastructure\Persistence\Models\AllocationModel;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    /**
     * Value-ref (AP-04): allocation_id → allocations.id — Eloquent only, no physical FK.
     *
     * @return BelongsTo<AllocationModel, $this>
     */
    public function allocation(): BelongsTo
    {
        return $this->belongsTo(AllocationModel::class, 'allocation_id');
    }

    /**
     * Value-ref (AP-04): operator_id → identity_users.id — Eloquent only, no physical FK.
     *
     * @return BelongsTo<UserModel, $this>
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'operator_id');
    }
}
