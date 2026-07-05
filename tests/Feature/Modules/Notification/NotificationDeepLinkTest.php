<?php

declare(strict_types=1);

use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use App\Modules\Notification\Application\Contracts\MarkNotificationReadContract;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Domain\Enums\DeliveryResultStatus;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Infrastructure\Adapters\InMemoryEmployeeExistenceReadAdapter;
use App\Modules\Notification\Infrastructure\Persistence\Models\NotificationLogModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function deepLinkActiveEmployees(string ...$employeeIds): void
{
    app()->instance(
        EmployeeExistenceReadPort::class,
        new InMemoryEmployeeExistenceReadAdapter(array_values($employeeIds)),
    );
}

it('persists entity reference and deep link route on delivery', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $requestId = UuidGenerator::uuid7();
    deepLinkActiveEmployees($employeeId);

    $result = app(NotificationDeliveryContract::class)->deliver(
        NotificationIntentDto::fromArray([
            'correlationId' => 'request:'.$requestId.':approved',
            'notificationType' => NotificationType::RequestApproved->value,
            'recipientEmployeeId' => $employeeId,
            'title' => 'درخواست تأیید شد',
            'message' => 'درخواست شما تأیید شد.',
            'sourceContext' => 'request',
            'entityType' => 'request',
            'entityId' => $requestId,
            'deepLinkRoute' => 'requests.show',
            'priority' => 'standard',
            'occurredAt' => '2026-07-02T10:30:00Z',
        ]),
    );

    expect($result->status)->toBe(DeliveryResultStatus::Delivered);

    $model = NotificationLogModel::query()->findOrFail($result->notificationId);
    expect($model->entity_type)->toBe('request');
    expect($model->entity_id)->toBe($requestId);
    expect($model->deep_link_route)->toBe('requests.show');

    $projection = app(NotificationInboxReadContract::class)->findByIdForRecipient(
        (string) $result->notificationId,
        $employeeId,
    );

    expect($projection)->not->toBeNull();
    $projection = $projection ?? throw new RuntimeException('Notification projection not found');
    expect($projection->entityType)->toBe('request');
    expect($projection->entityId)->toBe($requestId);
    expect($projection->deepLinkRoute)->toBe('requests.show');
});

it('delivers notifications without entity reference or deep link', function (): void {
    $employeeId = UuidGenerator::uuid7();
    deepLinkActiveEmployees($employeeId);

    $result = app(NotificationDeliveryContract::class)->deliver(
        NotificationIntentDto::fromArray([
            'correlationId' => 'request:no-link:001',
            'notificationType' => NotificationType::RequestApproved->value,
            'recipientEmployeeId' => $employeeId,
            'title' => 'اعلان بدون لینک',
            'message' => 'بدون مرجع موجودیت.',
            'sourceContext' => 'request',
            'priority' => 'standard',
            'occurredAt' => '2026-07-02T10:30:00Z',
        ]),
    );

    expect($result->status)->toBe(DeliveryResultStatus::Delivered);

    $projection = app(NotificationInboxReadContract::class)->findByIdForRecipient(
        (string) $result->notificationId,
        $employeeId,
    );

    expect($projection)->not->toBeNull();
    $projection = $projection ?? throw new RuntimeException('Notification projection not found');
    expect($projection->entityType)->toBeNull();
    expect($projection->entityId)->toBeNull();
    expect($projection->deepLinkRoute)->toBeNull();
});

it('does not mutate entity reference when marking a notification as read', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $allocationId = UuidGenerator::uuid7();
    deepLinkActiveEmployees($employeeId);

    $result = app(NotificationDeliveryContract::class)->deliver(
        NotificationIntentDto::fromArray([
            'correlationId' => 'allocation:'.$allocationId.':success',
            'notificationType' => NotificationType::AllocationSuccessful->value,
            'recipientEmployeeId' => $employeeId,
            'title' => 'تخصیص موفق',
            'message' => 'تخصیص خوابگاه انجام شد.',
            'sourceContext' => 'allocation',
            'entityType' => 'allocation',
            'entityId' => $allocationId,
            'deepLinkRoute' => 'allocations.show',
            'priority' => 'standard',
            'occurredAt' => '2026-07-02T10:30:00Z',
        ]),
    );

    $notificationId = (string) $result->notificationId;

    app(MarkNotificationReadContract::class)->markRead(
        $notificationId,
        $employeeId,
        new DateTimeImmutable('2026-07-02T12:00:00Z', new DateTimeZone('UTC')),
    );

    $model = NotificationLogModel::query()->findOrFail($notificationId);
    expect($model->entity_type)->toBe('allocation');
    expect($model->entity_id)->toBe($allocationId);
    expect($model->deep_link_route)->toBe('allocations.show');

    $projection = app(NotificationInboxReadContract::class)->findByIdForRecipient($notificationId, $employeeId);
    expect($projection)->not->toBeNull();
    $projection = $projection ?? throw new RuntimeException('Notification projection not found');
    expect($projection->isRead)->toBeTrue();
    expect($projection->entityType)->toBe('allocation');
    expect($projection->entityId)->toBe($allocationId);
    expect($projection->deepLinkRoute)->toBe('allocations.show');
});
