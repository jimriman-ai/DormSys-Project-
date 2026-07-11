<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Persistence\Models;

use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $floor_id
 * @property string $code
 * @property string $name
 * @property int $capacity_total
 * @property ResourceStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class RoomModel extends BaseModel
{
    protected $table = 'dormitory_rooms';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'floor_id',
        'code',
        'name',
        'capacity_total',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'capacity_total' => 'integer',
            'status' => ResourceStatus::class,
        ]);
    }

    /**
     * @return BelongsTo<FloorModel, $this>
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(FloorModel::class, 'floor_id');
    }

    /**
     * @return HasMany<BedModel, $this>
     */
    public function beds(): HasMany
    {
        return $this->hasMany(BedModel::class, 'room_id');
    }
}
