<?php

declare(strict_types=1);

namespace App\Modules\Notification\Domain\Models;

use App\Modules\Notification\Domain\Enums\DeliveryPriority;
use App\Modules\Notification\Domain\Enums\DeliveryStatus;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Domain\ValueObjects\CorrelationId;
use App\Modules\Notification\Domain\ValueObjects\EntityReference;
use App\Modules\Notification\Domain\ValueObjects\NotificationId;
use DateTimeImmutable;

final class Notification
{
    public function __construct(
        public readonly ?NotificationId $id,
        public readonly CorrelationId $correlationId,
        public readonly NotificationType $type,
        public readonly string $recipientEmployeeId,
        public readonly string $title,
        public readonly string $message,
        public readonly ?EntityReference $entityReference,
        public readonly ?string $deepLinkRoute,
        public readonly string $sourceContext,
        public readonly DeliveryPriority $priority,
        public readonly ?DateTimeImmutable $readAt,
        public readonly ?DateTimeImmutable $archivedAt,
        public readonly DeliveryStatus $deliveryStatus,
        public readonly ?string $skipReason,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public function requireId(): NotificationId
    {
        if ($this->id === null) {
            throw new \LogicException('Notification identifier is not assigned.');
        }

        return $this->id;
    }

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }

    public function isArchived(): bool
    {
        return $this->archivedAt !== null;
    }

    public static function deliver(
        CorrelationId $correlationId,
        NotificationType $type,
        string $recipientEmployeeId,
        string $title,
        string $message,
        string $sourceContext,
        DeliveryPriority $priority,
        DateTimeImmutable $createdAt,
        ?EntityReference $entityReference = null,
        ?string $deepLinkRoute = null,
    ): self {
        return new self(
            id: null,
            correlationId: $correlationId,
            type: $type,
            recipientEmployeeId: $recipientEmployeeId,
            title: $title,
            message: $message,
            entityReference: $entityReference,
            deepLinkRoute: $deepLinkRoute,
            sourceContext: $sourceContext,
            priority: $priority,
            readAt: null,
            archivedAt: null,
            deliveryStatus: DeliveryStatus::Delivered,
            skipReason: null,
            createdAt: $createdAt,
        );
    }

    public function markRead(DateTimeImmutable $readAt): self
    {
        if ($this->readAt !== null) {
            return $this;
        }

        return new self(
            id: $this->id,
            correlationId: $this->correlationId,
            type: $this->type,
            recipientEmployeeId: $this->recipientEmployeeId,
            title: $this->title,
            message: $this->message,
            entityReference: $this->entityReference,
            deepLinkRoute: $this->deepLinkRoute,
            sourceContext: $this->sourceContext,
            priority: $this->priority,
            readAt: $readAt,
            archivedAt: $this->archivedAt,
            deliveryStatus: $this->deliveryStatus,
            skipReason: $this->skipReason,
            createdAt: $this->createdAt,
        );
    }
}
