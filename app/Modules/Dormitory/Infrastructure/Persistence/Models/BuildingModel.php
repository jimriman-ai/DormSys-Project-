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
 * @property string $dormitory_id
 * @property string $code
 * @property string $name
 * @property ResourceStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class BuildingModel extends BaseModel
{
    protected $table = 'dormitory_buildings';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'dormitory_id',
        'code',
        'name',
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
     * @return BelongsTo<DormitoryModel, $this>
     */
    public function dormitory(): BelongsTo
    {
        return $this->belongsTo(DormitoryModel::class, 'dormitory_id');
    }

    /**
     * @return HasMany<FloorModel, $this>
     */
    public function floors(): HasMany
    {
        return $this->hasMany(FloorModel::class, 'building_id');
    }
}
