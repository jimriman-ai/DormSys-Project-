<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Persistence\Models;

use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $correlation_id
 * @property string $source_audit_log_id
 * @property Carbon $occurred_at
 * @property string $entity_type
 * @property string $entity_id
 * @property string $actor_type
 * @property string $actor_id
 * @property string $event_type
 * @property string $source_context
 * @property ArchiveVisibilityTier $archive_visibility_tier
 * @property Carbon $ingested_at
 */
class CorrelationProjectionEntryModel extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $table = 'reporting_correlation_projection_entries';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'correlation_id',
        'source_audit_log_id',
        'occurred_at',
        'entity_type',
        'entity_id',
        'actor_type',
        'actor_id',
        'event_type',
        'source_context',
        'archive_visibility_tier',
        'ingested_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'archive_visibility_tier' => ArchiveVisibilityTier::class,
            'ingested_at' => 'datetime',
        ];
    }

    /**
     * Value-ref (AP-04): source_audit_log_id → audit_logs.id — Eloquent only, no physical FK.
     *
     * @return BelongsTo<AuditLogModel, $this>
     */
    public function sourceAuditLog(): BelongsTo
    {
        return $this->belongsTo(AuditLogModel::class, 'source_audit_log_id');
    }
}
