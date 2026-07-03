<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Persistence\Models;

use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionCursorStatus;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Enums\RefreshMode;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property ProjectionFamily $projection_family
 * @property ArchiveVisibilityTier $archive_visibility_tier
 * @property string|null $last_source_audit_log_id
 * @property Carbon|null $last_occurred_at
 * @property string $projection_version
 * @property Carbon|null $refreshed_at
 * @property RefreshMode $refresh_mode
 * @property ProjectionCursorStatus $status
 * @property string|null $last_error
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ProjectionCursorModel extends Model
{
    use HasUuid;

    protected $table = 'reporting_projection_cursors';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'projection_family',
        'archive_visibility_tier',
        'last_source_audit_log_id',
        'last_occurred_at',
        'projection_version',
        'refreshed_at',
        'refresh_mode',
        'status',
        'last_error',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'projection_family' => ProjectionFamily::class,
            'archive_visibility_tier' => ArchiveVisibilityTier::class,
            'last_occurred_at' => 'datetime',
            'refreshed_at' => 'datetime',
            'refresh_mode' => RefreshMode::class,
            'status' => ProjectionCursorStatus::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
