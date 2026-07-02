<?php

declare(strict_types=1);

use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use App\Modules\Notification\Application\Contracts\MarkNotificationReadContract;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Infrastructure\Adapters\InMemoryEmployeeExistenceReadAdapter;
use App\Modules\Notification\Infrastructure\Persistence\Models\NotificationLogModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\Exceptions\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function inboxActiveEmployees(string ...$employeeIds): void
{
    app()->instance(
        EmployeeExistenceReadPort::class,
        new InMemoryEmployeeExistenceReadAdapter($employeeIds),
    );
}

function deliverInboxNotification(string $employeeId, string $correlationId, string $title): string
{
    $result = app(NotificationDeliveryContract::class)->deliver(
        NotificationIntentDto::fromArray([
            'correlationId' => $correlationId,
            'notificationType' => NotificationType::RequestApproved->value,
            'recipientEmployeeId' => $employeeId,
            'title' => $title,
            'message' => 'پیام آزمایشی',
            'sourceContext' => 'request',
            'priority' => 'standard',
            'occurredAt' => '2026-07-02T10:30:00Z',
        ]),
    );

    return (string) $result->notificationId;
}

it('lists inbox notifications for a recipient', function (): void {
    $employeeId = UuidGenerator::uuid7();
    inboxActiveEmployees($employeeId);

    deliverInboxNotification($employeeId, 'inbox:list:001', 'اعلان اول');
    deliverInboxNotification($employeeId, 'inbox:list:002', 'اعلان دوم');

    $inbox = app(NotificationInboxReadContract::class)->listForRecipient($employeeId);

    expect($inbox)->toHaveCount(2);
    $titles = array_map(fn ($item) => $item->title, $inbox);
    expect($titles)->toContain('اعلان اول', 'اعلان دوم');
});

it('filters unread notifications', function (): void {
    $employeeId = UuidGenerator::uuid7();
    inboxActiveEmployees($employeeId);

    $firstId = deliverInboxNotification($employeeId, 'inbox:unread:001', 'خوانده نشده');
    deliverInboxNotification($employeeId, 'inbox:unread:002', 'دیگری');

    app(MarkNotificationReadContract::class)->markRead(
        $firstId,
        $employeeId,
        new DateTimeImmutable('2026-07-02T11:00:00Z', new DateTimeZone('UTC')),
    );

    $unread = app(NotificationInboxReadContract::class)->listForRecipient($employeeId, unreadOnly: true);

    expect($unread)->toHaveCount(1);
    expect($unread[0]->title)->toBe('دیگری');
    expect(app(NotificationInboxReadContract::class)->countUnread($employeeId))->toBe(1);
});

it('marks a notification as read', function (): void {
    $employeeId = UuidGenerator::uuid7();
    inboxActiveEmployees($employeeId);

    $notificationId = deliverInboxNotification($employeeId, 'inbox:read:001', 'برای خواندن');

    app(MarkNotificationReadContract::class)->markRead(
        $notificationId,
        $employeeId,
        new DateTimeImmutable('2026-07-02T12:00:00Z', new DateTimeZone('UTC')),
    );

    $projection = app(NotificationInboxReadContract::class)->findByIdForRecipient($notificationId, $employeeId);

    expect($projection)->not->toBeNull();
    expect($projection->isRead)->toBeTrue();
    expect($projection->readAt?->format(DateTimeImmutable::ATOM))->toBe('2026-07-02T12:00:00+00:00');
});

it('denies cross-recipient inbox access', function (): void {
    $employeeA = UuidGenerator::uuid7();
    $employeeB = UuidGenerator::uuid7();
    inboxActiveEmployees($employeeA, $employeeB);

    $notificationId = deliverInboxNotification($employeeA, 'inbox:isolation:001', 'خصوصی');

    expect(app(NotificationInboxReadContract::class)->findByIdForRecipient($notificationId, $employeeB))->toBeNull();

    expect(fn () => app(MarkNotificationReadContract::class)->markRead(
        $notificationId,
        $employeeB,
        new DateTimeImmutable('2026-07-02T12:00:00Z', new DateTimeZone('UTC')),
    ))->toThrow(ValidationException::class);
});

it('excludes archived notifications from the default inbox list', function (): void {
    $employeeId = UuidGenerator::uuid7();
    inboxActiveEmployees($employeeId);

    $notificationId = deliverInboxNotification($employeeId, 'inbox:archive:001', 'آرشیو شده');

    NotificationLogModel::query()
        ->whereKey($notificationId)
        ->update(['archived_at' => '2026-07-02T13:00:00Z']);

    expect(app(NotificationInboxReadContract::class)->listForRecipient($employeeId))->toBe([]);
});

it('returns an empty inbox without error', function (): void {
    $employeeId = UuidGenerator::uuid7();

    expect(app(NotificationInboxReadContract::class)->listForRecipient($employeeId))->toBe([]);
    expect(app(NotificationInboxReadContract::class)->countUnread($employeeId))->toBe(0);
});
