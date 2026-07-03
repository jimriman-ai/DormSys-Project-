<?php

declare(strict_types=1);

namespace App\Modules\Audit\Infrastructure\Persistence\Models;

use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\Exceptions\AppendOnlyViolationException;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Append-only audit trail — no updates or deletes (AP-06).
 *
 * @property string $id
 * @property string $correlation_id
 * @property AuditEventType $event_type
 * @property string $entity_type
 * @property string $entity_id
 * @property ActorType $actor_type
 * @property string $actor_id
 * @property string $source_context
 * @property array<string, mixed>|null $old_values
 * @property array<string, mixed>|null $new_values
 * @property array<string, mixed>|null $metadata
 * @property string $payload_hash
 * @property Carbon $occurred_at
 * @property Carbon|null $archived_at
 * @property Carbon $created_at
 */
class AuditLogModel extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $table = 'audit_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'correlation_id',
        'event_type',
        'entity_type',
        'entity_id',
        'actor_type',
        'actor_id',
        'source_context',
        'old_values',
        'new_values',
        'metadata',
        'payload_hash',
        'occurred_at',
        'archived_at',
        'created_at',
    ];

    protected static function booted(): void
    {
        static::updating(static function (): void {
            throw new AppendOnlyViolationException('Audit log records are append-only.');
        });

        static::deleting(static function (): void {
            throw new AppendOnlyViolationException('Audit log records are append-only.');
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_type' => AuditEventType::class,
            'actor_type' => ActorType::class,
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
            'occurred_at' => 'datetime',
            'archived_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
