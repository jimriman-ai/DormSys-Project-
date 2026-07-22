<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Persistence\Models;

use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property ProjectionFamily $projection_family
 * @property string $source_audit_log_id
 * @property ArchiveVisibilityTier $archive_visibility_tier
 * @property Carbon $ingested_at
 */
class ProjectionIngestReceiptModel extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $table = 'reporting_projection_ingest_receipts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'projection_family',
        'source_audit_log_id',
        'archive_visibility_tier',
        'ingested_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'projection_family' => ProjectionFamily::class,
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
