<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Persistence\Models;

use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $room_id
 * @property string $label
 * @property ResourceStatus $status
 * @property PhysicalOccupancyState $physical_occupancy_state
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class BedModel extends BaseModel
{
    protected $table = 'dormitory_beds';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'room_id',
        'label',
        'status',
        'physical_occupancy_state',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'status' => ResourceStatus::class,
            'physical_occupancy_state' => PhysicalOccupancyState::class,
        ]);
    }

    /**
     * @return BelongsTo<RoomModel, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(RoomModel::class, 'room_id');
    }
}
