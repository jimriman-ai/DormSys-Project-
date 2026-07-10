<?php

declare(strict_types=1);

use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use App\Modules\Notification\Application\Contracts\MarkNotificationReadContract;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;
use App\Modules\Notification\Application\DTOs\NotificationInboxListQueryDTO;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Application\DTOs\NotificationProjectionDto;
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
        new InMemoryEmployeeExistenceReadAdapter(array_values($employeeIds)),
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
    if (! $projection instanceof NotificationProjectionDto) {
        throw new UnexpectedValueException('Expected inbox projection.');
    }

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

it('returns a paginated envelope with authoritative metadata', function (): void {
    $employeeId = UuidGenerator::uuid7();
    inboxActiveEmployees($employeeId);

    deliverInboxNotification($employeeId, 'inbox:page:meta:001', 'اعلان یک');
    deliverInboxNotification($employeeId, 'inbox:page:meta:002', 'اعلان دو');

    $page = app(NotificationInboxReadContract::class)->listForRecipientPaginated(
        new NotificationInboxListQueryDTO(recipientEmployeeId: $employeeId),
    );

    expect($page->items)->toHaveCount(2);
    expect($page->total)->toBe(2);
    expect($page->currentPage)->toBe(1);
    expect($page->perPage)->toBe(50);
    expect($page->lastPage)->toBe(1);
});

it('orders paginated inbox by created_at desc then id desc', function (): void {
    $employeeId = UuidGenerator::uuid7();
    inboxActiveEmployees($employeeId);

    $olderId = deliverInboxNotification($employeeId, 'inbox:order:001', 'قدیمی‌تر');
    $newerId = deliverInboxNotification($employeeId, 'inbox:order:002', 'جدیدتر');

    NotificationLogModel::query()->whereKey($olderId)->update(['created_at' => '2026-07-01T10:00:00Z']);
    NotificationLogModel::query()->whereKey($newerId)->update(['created_at' => '2026-07-02T10:00:00Z']);

    $sameMomentA = deliverInboxNotification($employeeId, 'inbox:order:003', 'هم‌زمان الف');
    $sameMomentB = deliverInboxNotification($employeeId, 'inbox:order:004', 'هم‌زمان ب');

    NotificationLogModel::query()->whereKey($sameMomentA)->update(['created_at' => '2026-07-03T10:00:00Z']);
    NotificationLogModel::query()->whereKey($sameMomentB)->update(['created_at' => '2026-07-03T10:00:00Z']);

    $page = app(NotificationInboxReadContract::class)->listForRecipientPaginated(
        new NotificationInboxListQueryDTO(recipientEmployeeId: $employeeId),
    );

    $ids = array_map(static fn (NotificationProjectionDto $item): string => $item->id, $page->items);

    $tieBreakerFirst = max($sameMomentA, $sameMomentB);
    $tieBreakerSecond = min($sameMomentA, $sameMomentB);

    expect($ids)->toBe([$tieBreakerFirst, $tieBreakerSecond, $newerId, $olderId]);
});

it('returns a distinct second page when total exceeds perPage', function (): void {
    $employeeId = UuidGenerator::uuid7();
    inboxActiveEmployees($employeeId);

    for ($index = 1; $index <= 51; $index++) {
        deliverInboxNotification($employeeId, 'inbox:slice:'.$index, 'صفحه اعلان '.$index);
    }

    $pageOne = app(NotificationInboxReadContract::class)->listForRecipientPaginated(
        new NotificationInboxListQueryDTO(recipientEmployeeId: $employeeId, page: 1, perPage: 50),
    );
    $pageTwo = app(NotificationInboxReadContract::class)->listForRecipientPaginated(
        new NotificationInboxListQueryDTO(recipientEmployeeId: $employeeId, page: 2, perPage: 50),
    );

    expect($pageOne->total)->toBe(51);
    expect($pageOne->lastPage)->toBe(2);
    expect($pageOne->items)->toHaveCount(50);
    expect($pageTwo->currentPage)->toBe(2);
    expect($pageTwo->items)->toHaveCount(1);

    $pageOneTitles = array_map(static fn (NotificationProjectionDto $item): string => $item->title, $pageOne->items);
    $pageTwoTitles = array_map(static fn (NotificationProjectionDto $item): string => $item->title, $pageTwo->items);

    expect($pageOneTitles)->not->toContain($pageTwoTitles[0]);
    expect(array_intersect($pageOneTitles, $pageTwoTitles))->toBe([]);
});

it('clamps out-of-range pages to the last page', function (): void {
    $employeeId = UuidGenerator::uuid7();
    inboxActiveEmployees($employeeId);

    for ($index = 1; $index <= 51; $index++) {
        deliverInboxNotification($employeeId, 'inbox:clamp:'.$index, 'اعلان '.$index);
    }

    $page = app(NotificationInboxReadContract::class)->listForRecipientPaginated(
        new NotificationInboxListQueryDTO(recipientEmployeeId: $employeeId, page: 99, perPage: 50),
    );

    expect($page->currentPage)->toBe(2);
    expect($page->lastPage)->toBe(2);
    expect($page->items)->toHaveCount(1);
});

it('keeps listForRecipient compatible alongside pagination', function (): void {
    $employeeId = UuidGenerator::uuid7();
    inboxActiveEmployees($employeeId);

    for ($index = 1; $index <= 51; $index++) {
        deliverInboxNotification($employeeId, 'inbox:compat:'.$index, 'سازگاری '.$index);
    }

    $flat = app(NotificationInboxReadContract::class)->listForRecipient($employeeId);
    $paginated = app(NotificationInboxReadContract::class)->listForRecipientPaginated(
        new NotificationInboxListQueryDTO(recipientEmployeeId: $employeeId),
    );

    expect($flat)->toHaveCount(50);
    expect($paginated->total)->toBe(51);
    expect($paginated->items)->toHaveCount(50);
    expect(array_map(static fn (NotificationProjectionDto $item): string => $item->id, $flat))
        ->toBe(array_map(static fn (NotificationProjectionDto $item): string => $item->id, $paginated->items));
});
