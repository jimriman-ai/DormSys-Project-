<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Persistence\Models;

use App\Modules\Dormitory\Infrastructure\Persistence\Models\BedModel;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $allocation_id
 * @property string $bed_id
 * @property int $sequence
 */
class AllocationItemModel extends BaseModel
{
    protected $table = 'allocation_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'allocation_id',
        'bed_id',
        'sequence',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'sequence' => 'integer',
        ]);
    }

    /**
     * @return BelongsTo<AllocationModel, $this>
     */
    public function allocation(): BelongsTo
    {
        return $this->belongsTo(AllocationModel::class, 'allocation_id');
    }

    /**
     * bed_id → dormitory_beds.id (physical FK present; Eloquent relation).
     *
     * @return BelongsTo<BedModel, $this>
     */
    public function bed(): BelongsTo
    {
        return $this->belongsTo(BedModel::class, 'bed_id');
    }
}
