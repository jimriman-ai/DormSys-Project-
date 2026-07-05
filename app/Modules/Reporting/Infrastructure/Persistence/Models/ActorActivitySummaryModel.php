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
 * @property string $actor_type
 * @property string $actor_id
 * @property Carbon $window_start
 * @property Carbon $window_end
 * @property WindowGranularity $granularity
 * @property int $event_count
 * @property list<string> $distinct_event_types
 * @property list<string> $distinct_entity_refs
 * @property int $distinct_entities_touched
 * @property ArchiveVisibilityTier $archive_visibility_tier
 * @property Carbon $refreshed_at
 * @property string $projection_version
 */
class ActorActivitySummaryModel extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $table = 'reporting_actor_activity_summaries';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'actor_type',
        'actor_id',
        'window_start',
        'window_end',
        'granularity',
        'event_count',
        'distinct_event_types',
        'distinct_entity_refs',
        'distinct_entities_touched',
        'archive_visibility_tier',
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
            'distinct_event_types' => 'array',
            'distinct_entity_refs' => 'array',
            'archive_visibility_tier' => ArchiveVisibilityTier::class,
            'refreshed_at' => 'datetime',
        ];
    }
}
