<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Persistence\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Employee ↔ dormitory assignment (Q-EMP-DORM Option B / WP-DASH-G02-R1).
 *
 * Soft revoke via revoked_at (not Eloquent SoftDeletes). Active = revoked_at IS NULL.
 *
 * @property string $id
 * @property string $user_id
 * @property string $dormitory_id
 * @property Carbon $assigned_at
 * @property Carbon|null $revoked_at
 */
class DormitoryAssignment extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $table = 'dormitory_assignments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'dormitory_id',
        'assigned_at',
        'revoked_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * @return BelongsTo<DormitoryModel, $this>
     */
    public function dormitory(): BelongsTo
    {
        return $this->belongsTo(DormitoryModel::class, 'dormitory_id');
    }
}
