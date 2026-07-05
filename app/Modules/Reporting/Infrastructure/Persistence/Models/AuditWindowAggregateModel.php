<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Persistence\Models;

use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\WindowGranularity;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property Carbon $window_start
 * @property Carbon $window_end
 * @property WindowGranularity $granularity
 * @property string|null $event_type
 * @property string|null $source_context
 * @property string|null $actor_type
 * @property string|null $entity_type
 * @property ArchiveVisibilityTier $archive_visibility_tier
 * @property int $event_count
 * @property int $distinct_entity_count
 * @property int $distinct_actor_count
 * @property list<string> $distinct_entity_refs
 * @property list<string> $distinct_actor_refs
 * @property array<string, int>|null $top_event_types
 * @property Carbon $refreshed_at
 * @property string $projection_version
 */
class AuditWindowAggregateModel extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $table = 'reporting_audit_window_aggregates';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'window_start',
        'window_end',
        'granularity',
        'event_type',
        'source_context',
        'actor_type',
        'entity_type',
        'archive_visibility_tier',
        'event_count',
        'distinct_entity_count',
        'distinct_actor_count',
        'distinct_entity_refs',
        'distinct_actor_refs',
        'top_event_types',
        'refreshed_at',
        'projection_version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'window_start' => 'datetime',
            'window_end' => 'datetime',
            'granularity' => WindowGranularity::class,
            'archive_visibility_tier' => ArchiveVisibilityTier::class,
            'distinct_entity_refs' => 'array',
            'distinct_actor_refs' => 'array',
            'top_event_types' => 'array',
            'refreshed_at' => 'datetime',
        ];
    }
}
