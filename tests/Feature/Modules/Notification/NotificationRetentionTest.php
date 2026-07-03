<?php

declare(strict_types=1);

use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Infrastructure\Adapters\InMemoryEmployeeExistenceReadAdapter;
use App\Modules\Notification\Infrastructure\Jobs\ArchiveExpiredNotificationsJob;
use App\Modules\Notification\Infrastructure\Persistence\Models\NotificationLogModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

function retentionActiveEmployees(string ...$employeeIds): void
{
    app()->instance(
        EmployeeExistenceReadPort::class,
        new InMemoryEmployeeExistenceReadAdapter($employeeIds),
    );
}

function deliverRetentionNotification(string $employeeId, string $correlationId): string
{
    $result = app(NotificationDeliveryContract::class)->deliver(
        NotificationIntentDto::fromArray([
            'correlationId' => $correlationId,
            'notificationType' => NotificationType::RequestApproved->value,
            'recipientEmployeeId' => $employeeId,
            'title' => 'اعلان نگهداری',
            'message' => 'پیام آزمایشی نگهداری.',
            'sourceContext' => 'request',
            'priority' => 'standard',
            'occurredAt' => '2026-07-02T10:30:00Z',
        ]),
    );

    return (string) $result->notificationId;
}

it('archives expired notifications and excludes them from the inbox', function (): void {
    $employeeId = UuidGenerator::uuid7();
    retentionActiveEmployees($employeeId);

    $expiredId = deliverRetentionNotification($employeeId, 'retention:expired:001');
    $recentId = deliverRetentionNotification($employeeId, 'retention:recent:001');

    NotificationLogModel::query()
        ->whereKey($expiredId)
        ->update(['created_at' => now()->subMonths(25)]);

    Bus::dispatchSync(new ArchiveExpiredNotificationsJob);

    expect(NotificationLogModel::query()->findOrFail($expiredId)->archived_at)->not->toBeNull();
    expect(NotificationLogModel::query()->findOrFail($recentId)->archived_at)->toBeNull();

    $inbox = app(NotificationInboxReadContract::class)->listForRecipient($employeeId);

    expect($inbox)->toHaveCount(1);
    expect($inbox[0]->id)->toBe($recentId);
});

it('does not hard delete archived notifications', function (): void {
    $employeeId = UuidGenerator::uuid7();
    retentionActiveEmployees($employeeId);

    $notificationId = deliverRetentionNotification($employeeId, 'retention:persist:001');

    NotificationLogModel::query()
        ->whereKey($notificationId)
        ->update(['created_at' => now()->subMonths(30)]);

    Bus::dispatchSync(new ArchiveExpiredNotificationsJob);

    expect(NotificationLogModel::withTrashed()->find($notificationId))->not->toBeNull();
    expect(NotificationLogModel::query()->find($notificationId))->not->toBeNull();
});
