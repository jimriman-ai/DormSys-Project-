<?php

declare(strict_types=1);

namespace App\Modules\Notification\Infrastructure\Repositories;

use App\Modules\Notification\Application\Contracts\NotificationRepositoryContract;
use App\Modules\Notification\Domain\Enums\DeliveryStatus;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Domain\Models\Notification;
use App\Modules\Notification\Domain\ValueObjects\CorrelationId;
use App\Modules\Notification\Domain\ValueObjects\EntityReference;
use App\Modules\Notification\Domain\ValueObjects\NotificationId;
use App\Modules\Notification\Infrastructure\Persistence\Models\NotificationLogModel;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Carbon;

final class NotificationRepository implements NotificationRepositoryContract
{
    public function save(Notification $notification): Notification
    {
        if ($notification->id === null) {
            return $this->insert($notification);
        }

        return $this->update($notification);
    }

    public function findByDedupKey(
        CorrelationId $correlationId,
        string $recipientEmployeeId,
        NotificationType $notificationType,
    ): ?Notification {
        $model = NotificationLogModel::query()
            ->where('correlation_id', $correlationId->value)
            ->where('recipient_employee_id', $recipientEmployeeId)
            ->where('notification_type', $notificationType->value)
            ->where('delivery_status', DeliveryStatus::Delivered->value)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByIdForRecipient(NotificationId $notificationId, string $recipientEmployeeId): ?Notification
    {
        $model = NotificationLogModel::query()
            ->whereKey($notificationId->value)
            ->where('recipient_employee_id', $recipientEmployeeId)
            ->where('delivery_status', DeliveryStatus::Delivered->value)
            ->whereNull('archived_at')
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function listForRecipient(string $recipientEmployeeId, ?bool $unreadOnly = null, int $limit = 50): array
    {
        $query = NotificationLogModel::query()
            ->where('recipient_employee_id', $recipientEmployeeId)
            ->where('delivery_status', DeliveryStatus::Delivered->value)
            ->whereNull('archived_at')
            ->orderByDesc('created_at')
            ->limit($limit);

        if ($unreadOnly === true) {
            $query->whereNull('read_at');
        }

        $notifications = [];

        foreach ($query->get() as $model) {
            $notifications[] = $this->toDomain($model);
        }

        return $notifications;
    }

    public function countUnread(string $recipientEmployeeId): int
    {
        return NotificationLogModel::query()
            ->where('recipient_employee_id', $recipientEmployeeId)
            ->where('delivery_status', DeliveryStatus::Delivered->value)
            ->whereNull('archived_at')
            ->whereNull('read_at')
            ->count();
    }

    public function archiveExpiredBefore(DateTimeImmutable $cutoff): int
    {
        $archivedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        return NotificationLogModel::query()
            ->whereNull('archived_at')
            ->where('delivery_status', DeliveryStatus::Delivered->value)
            ->where('created_at', '<', $cutoff)
            ->update(['archived_at' => $archivedAt]);
    }

    private function insert(Notification $notification): Notification
    {
        $model = new NotificationLogModel([
            'correlation_id' => $notification->correlationId->value,
            'notification_type' => $notification->type->value,
            'recipient_employee_id' => $notification->recipientEmployeeId,
            'title' => $notification->title,
            'message' => $notification->message,
            'entity_type' => $notification->entityReference?->entityType,
            'entity_id' => $notification->entityReference?->entityId,
            'deep_link_route' => $notification->deepLinkRoute,
            'source_context' => $notification->sourceContext,
            'priority' => $notification->priority->value,
            'read_at' => $notification->readAt,
            'archived_at' => $notification->archivedAt,
            'delivery_status' => $notification->deliveryStatus->value,
            'skip_reason' => $notification->skipReason,
        ]);

        $model->created_at = Carbon::instance($notification->createdAt);
        $model->save();
        $model->refresh();

        return $this->toDomain($model);
    }

    private function update(Notification $notification): Notification
    {
        $model = NotificationLogModel::query()->findOrFail($notification->requireId()->value);

        $model->fill([
            'read_at' => $notification->readAt,
            'archived_at' => $notification->archivedAt,
        ]);
        $model->save();
        $model->refresh();

        return $this->toDomain($model);
    }

    private function toDomain(NotificationLogModel $model): Notification
    {
        $entityReference = null;

        if ($model->entity_type !== null && $model->entity_id !== null) {
            $entityReference = EntityReference::fromStrings($model->entity_type, $model->entity_id);
        }

        return new Notification(
            id: NotificationId::fromString($model->getId()),
            correlationId: CorrelationId::fromString($model->correlation_id),
            type: $model->notification_type,
            recipientEmployeeId: $model->recipient_employee_id,
            title: $model->title,
            message: $model->message,
            entityReference: $entityReference,
            deepLinkRoute: $model->deep_link_route,
            sourceContext: $model->source_context,
            priority: $model->priority,
            readAt: $this->toImmutable($model->read_at),
            archivedAt: $this->toImmutable($model->archived_at),
            deliveryStatus: $model->delivery_status,
            skipReason: $model->skip_reason,
            createdAt: $this->toImmutable($model->created_at) ?? new DateTimeImmutable('now', new DateTimeZone('UTC')),
        );
    }

    private function toImmutable(mixed $value): ?DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        return DateTimeImmutable::createFromInterface($value);
    }
}
