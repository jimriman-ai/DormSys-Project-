<?php

declare(strict_types=1);

namespace App\Models\Dormitory;

use App\Modules\Dormitory\Infrastructure\Persistence\Models\RoomModel;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Unit-manager ↔ room assignment pivot (BL-B1-01 / RM-02).
 *
 * Placement: App\Models\Dormitory (MANAGER-ASSIGN-CREATE).
 * Extends Model+HasUuid (not BaseModel): table has no deleted_at / audit actor columns.
 * HasAuditActors omitted: trait not present on disk (DOM-GAP-06 CLOSED UUID-only).
 * Room target: RoomModel (dormitory_rooms) — no DormitoryRoom class in codebase.
 *
 * @property string $id
 * @property string $user_id
 * @property string $room_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class DormitoryUnitManagerAssignment extends Model
{
    use HasUuid;

    protected $table = 'dormitory_unit_manager_assignments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'room_id',
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
     * FK: room_id → dormitory_rooms.id (restrictOnDelete).
     *
     * @return BelongsTo<RoomModel, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(RoomModel::class, 'room_id');
    }
}
