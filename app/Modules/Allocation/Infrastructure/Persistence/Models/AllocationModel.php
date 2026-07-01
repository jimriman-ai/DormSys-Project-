<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Persistence\Models;

use App\Modules\Allocation\Domain\Enums\AllocationMethod;
use App\Modules\Allocation\Domain\Enums\AllocationStatus;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;

/**
 * @property string $id
 * @property string $person_id
 * @property string $bed_id
 * @property string $date_range
 * @property AllocationMethod $method
 * @property AllocationStatus $status
 * @property string|null $source_request_id
 * @property string|null $source_lottery_result_id
 * @property Carbon|null $released_at
 * @property string|null $release_reason
 */
class AllocationModel extends BaseModel
{
    protected $table = 'allocations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'person_id',
        'bed_id',
        'date_range',
        'method',
        'status',
        'source_request_id',
        'source_lottery_result_id',
        'released_at',
        'release_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'method' => AllocationMethod::class,
            'status' => AllocationStatus::class,
            'released_at' => 'datetime',
        ]);
    }

    /**
     * @return HasMany<AllocationItemModel, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(AllocationItemModel::class, 'allocation_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('allocation')
            ->logOnly([
                'person_id',
                'bed_id',
                'date_range',
                'method',
                'status',
                'released_at',
                'release_reason',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent('created', 'allocation assigned')
            ->setDescriptionForEvent('updated', 'allocation updated');
    }
}
