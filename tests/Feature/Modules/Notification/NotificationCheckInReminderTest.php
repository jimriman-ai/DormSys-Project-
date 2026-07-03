<?php

declare(strict_types=1);

use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Domain\Enums\DeliveryPriority;
use App\Modules\Notification\Domain\Enums\DeliveryResultStatus;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Infrastructure\Adapters\InMemoryEmployeeExistenceReadAdapter;
use App\Modules\Notification\Infrastructure\Persistence\Models\NotificationLogModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function reminderActiveEmployees(string ...$employeeIds): void
{
    app()->instance(
        EmployeeExistenceReadPort::class,
        new InMemoryEmployeeExistenceReadAdapter($employeeIds),
    );
}

it('delivers a synthetic check in reminder intent with standard priority', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $allocationId = UuidGenerator::uuid7();
    reminderActiveEmployees($employeeId);

    $correlationId = 'check_in:'.$allocationId.':reminder:2026-07-05';

    $result = app(NotificationDeliveryContract::class)->deliver(
        NotificationIntentDto::fromArray([
            'correlationId' => $correlationId,
            'notificationType' => NotificationType::CheckInReminder->value,
            'recipientEmployeeId' => $employeeId,
            'title' => 'یادآوری ورود به خوابگاه',
            'message' => 'لطفاً تا تاریخ مقرر وارد خوابگاه شوید.',
            'sourceContext' => 'check_in',
            'entityType' => 'allocation',
            'entityId' => $allocationId,
            'deepLinkRoute' => 'check-in.show',
            'priority' => DeliveryPriority::Standard->value,
            'occurredAt' => '2026-07-02T08:00:00Z',
        ]),
    );

    expect($result->status)->toBe(DeliveryResultStatus::Delivered);
    expect($result->notificationId)->not->toBeNull();

    $model = NotificationLogModel::query()->findOrFail($result->notificationId);
    expect($model->notification_type)->toBe(NotificationType::CheckInReminder);
    expect($model->correlation_id)->toBe($correlationId);
    expect($model->priority)->toBe(DeliveryPriority::Standard);
    expect($model->source_context)->toBe('check_in');

    $projection = app(NotificationInboxReadContract::class)->findByIdForRecipient(
        (string) $result->notificationId,
        $employeeId,
    );

    expect($projection)->not->toBeNull();
    expect($projection->notificationType)->toBe(NotificationType::CheckInReminder->value);
    expect($projection->entityId)->toBe($allocationId);
    expect($projection->deepLinkRoute)->toBe('check-in.show');
});

it('deduplicates replayed check in reminder intents', function (): void {
    $employeeId = UuidGenerator::uuid7();
    $allocationId = UuidGenerator::uuid7();
    reminderActiveEmployees($employeeId);

    $correlationId = 'check_in:'.$allocationId.':reminder:2026-07-05';

    $intent = NotificationIntentDto::fromArray([
        'correlationId' => $correlationId,
        'notificationType' => NotificationType::CheckInReminder->value,
        'recipientEmployeeId' => $employeeId,
        'title' => 'یادآوری ورود',
        'message' => 'یادآوری ورود به خوابگاه.',
        'sourceContext' => 'check_in',
        'priority' => DeliveryPriority::Standard->value,
        'occurredAt' => '2026-07-02T08:00:00Z',
    ]);

    $delivery = app(NotificationDeliveryContract::class);
    $first = $delivery->deliver($intent);
    $second = $delivery->deliver($intent);

    expect($first->status)->toBe(DeliveryResultStatus::Delivered);
    expect($second->status)->toBe(DeliveryResultStatus::Duplicate);
    expect(NotificationLogModel::query()->where('notification_type', NotificationType::CheckInReminder)->count())->toBe(1);
});
