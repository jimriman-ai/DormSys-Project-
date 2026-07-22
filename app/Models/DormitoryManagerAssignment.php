<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Manager ↔ dormitory assignment pivot (BL-B1-01 / RM-01).
 *
 * @property string $id
 * @property string $user_id
 * @property string $dormitory_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class DormitoryManagerAssignment extends Model
{
    use HasUuid;

    protected $table = 'dormitory_manager_assignments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'dormitory_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * FK: user_id → identity_users.id (restrictOnDelete).
     *
     * @return BelongsTo<UserModel, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    /**
     * FK: dormitory_id → dormitories.id (restrictOnDelete).
     *
     * @return BelongsTo<DormitoryModel, $this>
     */
    public function dormitory(): BelongsTo
    {
        return $this->belongsTo(DormitoryModel::class, 'dormitory_id');
    }
}
