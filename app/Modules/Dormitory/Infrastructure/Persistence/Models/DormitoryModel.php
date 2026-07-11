<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Persistence\Models;

use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property ResourceStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class DormitoryModel extends BaseModel
{
    protected $table = 'dormitories';

    /**
     * @var list<string>
     */
    protected $fillable = [
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
     * @return HasMany<BuildingModel, $this>
     */
    public function buildings(): HasMany
    {
        return $this->hasMany(BuildingModel::class, 'dormitory_id');
    }
}
