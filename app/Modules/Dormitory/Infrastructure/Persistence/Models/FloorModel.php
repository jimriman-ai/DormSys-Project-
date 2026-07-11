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
 * @property string $building_id
 * @property string $label
 * @property ResourceStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class FloorModel extends BaseModel
{
    protected $table = 'dormitory_floors';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'building_id',
        'label',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'status' => ResourceStatus::class,
        ]);
    }

    /**
     * @return BelongsTo<BuildingModel, $this>
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(BuildingModel::class, 'building_id');
    }

    /**
     * @return HasMany<RoomModel, $this>
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(RoomModel::class, 'floor_id');
    }
}
