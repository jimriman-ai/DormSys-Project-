<?php

declare(strict_types=1);

namespace App\Modules\Audit\Infrastructure\Listeners;

use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\ValueObjects\ActorReference;
use App\Modules\Audit\Domain\ValueObjects\CorrelationId;
use App\Modules\Audit\Domain\ValueObjects\EntityReference;
use DateTimeImmutable;
use Spatie\Activitylog\Models\Activity;

final class ActivityLogAuditBridge
{
    public function __construct(
        private readonly AuditRecordingContract $auditRecording,
    ) {}

    public function handle(Activity $activity): void
    {
        if (! (bool) config('audit.activity_bridge_enabled', false)) {
            return;
        }

        $eventType = $this->resolveEventType($activity);

        if ($eventType === null) {
            return;
        }

        $entity = $this->resolveEntityReference($activity);

        if ($entity === null) {
            return;
        }

        $this->auditRecording->record(new AuditEntryDto(
            correlationId: CorrelationId::fromString('activity:'.$activity->id),
            eventType: $eventType,
            entityReference: $entity,
            actorReference: $this->resolveActorReference($activity),
            sourceContext: $activity->log_name ?? 'activity_bridge',
            oldValues: $this->extractSnapshot($activity, 'old'),
            newValues: $this->extractSnapshot($activity, 'new'),
            metadata: [
                'bridge' => 'activity_log',
                'activity_id' => $activity->id,
                'activity_event' => $activity->event,
                'activity_description' => $activity->description,
            ],
            occurredAt: $activity->created_at?->toDateTimeImmutable()
                ?? now('UTC')->toDateTimeImmutable(),
        ));
    }

    private function resolveEventType(Activity $activity): ?AuditEventType
    {
        $entityType = $this->normalizeEntityType($activity->subject_type);

        if ($entityType === 'identity_user') {
            return match ($activity->event) {
                'created' => AuditEventType::IdentityUserCreated,
                'updated' => AuditEventType::IdentityUserDeactivated,
                default => null,
            };
        }

        return null;
    }

    private function resolveEntityReference(Activity $activity): ?EntityReference
    {
        if ($activity->subject_type === null || $activity->subject_id === null) {
            return null;
        }

        $entityType = $this->normalizeEntityType($activity->subject_type);

        if ($entityType === null) {
            return null;
        }

        return EntityReference::fromStrings($entityType, (string) $activity->subject_id);
    }

    private function normalizeEntityType(?string $subjectType): ?string
    {
        if ($subjectType === null) {
            return null;
        }

        if (str_ends_with($subjectType, 'UserModel')) {
            return 'identity_user';
        }

        return class_basename($subjectType);
    }

    private function resolveActorReference(Activity $activity): ActorReference
    {
        if ($activity->causer_id !== null) {
            return new ActorReference(ActorType::User, (string) $activity->causer_id);
        }

        return new ActorReference(ActorType::System, 'system:scheduler');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractSnapshot(Activity $activity, string $key): ?array
    {
        $properties = $activity->properties?->toArray() ?? [];

        if ($key === 'old') {
            $values = $properties['old'] ?? $properties['attributes'] ?? null;

            return is_array($values) ? $values : null;
        }

        $values = $properties['attributes'] ?? null;

        return is_array($values) ? $values : null;
    }
}
