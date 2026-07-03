<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\DTOs;

use App\Modules\Notification\Domain\Enums\DeliveryPriority;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Domain\ValueObjects\CorrelationId;
use App\Modules\Notification\Domain\ValueObjects\EntityReference;
use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final readonly class NotificationIntentDto
{
    public function __construct(
        public CorrelationId $correlationId,
        public NotificationType $notificationType,
        public string $recipientEmployeeId,
        public string $title,
        public string $message,
        public string $sourceContext,
        public DeliveryPriority $priority,
        public DateTimeImmutable $occurredAt,
        public ?EntityReference $entityReference = null,
        public ?string $deepLinkRoute = null,
    ) {
        if ($title === '' || strlen($title) > 255) {
            throw new ValidationException('Notification title is required.');
        }

        if ($message === '') {
            throw new ValidationException('Notification message is required.');
        }

        if ($sourceContext === '' || strlen($sourceContext) > 32) {
            throw new ValidationException('Source context is required.');
        }

        if (! Uuid::isValid($recipientEmployeeId)) {
            throw new ValidationException('Invalid recipient employee identifier.');
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $entityReference = null;

        if (isset($payload['entityType'], $payload['entityId'])
            && is_string($payload['entityType'])
            && is_string($payload['entityId'])) {
            $entityReference = EntityReference::fromStrings($payload['entityType'], $payload['entityId']);
        }

        return new self(
            correlationId: CorrelationId::fromString((string) $payload['correlationId']),
            notificationType: NotificationType::from((string) $payload['notificationType']),
            recipientEmployeeId: (string) $payload['recipientEmployeeId'],
            title: (string) $payload['title'],
            message: (string) $payload['message'],
            sourceContext: (string) $payload['sourceContext'],
            priority: DeliveryPriority::from((string) ($payload['priority'] ?? DeliveryPriority::Standard->value)),
            occurredAt: self::parseOccurredAt($payload['occurredAt'] ?? null),
            entityReference: $entityReference,
            deepLinkRoute: isset($payload['deepLinkRoute']) ? (string) $payload['deepLinkRoute'] : null,
        );
    }

    private static function parseOccurredAt(mixed $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return new DateTimeImmutable($value);
        }

        return new DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
