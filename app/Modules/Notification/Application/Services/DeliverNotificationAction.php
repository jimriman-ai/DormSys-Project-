<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\Services;

use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\Contracts\NotificationRepositoryContract;
use App\Modules\Notification\Application\DTOs\NotificationDeliveryResultDto;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Domain\Enums\DeliveryResultStatus;
use App\Modules\Notification\Domain\Models\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class DeliverNotificationAction implements NotificationDeliveryContract
{
    public function __construct(
        private readonly EmployeeExistenceReadPort $employeeExistence,
        private readonly NotificationRepositoryContract $notifications,
    ) {}

    public function deliver(NotificationIntentDto $intent): NotificationDeliveryResultDto
    {
        if (! $this->employeeExistence->existsActiveEmployee($intent->recipientEmployeeId)) {
            return new NotificationDeliveryResultDto(
                notificationId: null,
                status: DeliveryResultStatus::Skipped,
                skipReason: 'skipped_invalid_recipient',
            );
        }

        $existing = $this->notifications->findByDedupKey(
            $intent->correlationId,
            $intent->recipientEmployeeId,
            $intent->notificationType,
        );

        if ($existing !== null) {
            return new NotificationDeliveryResultDto(
                notificationId: $existing->requireId()->value,
                status: DeliveryResultStatus::Duplicate,
            );
        }

        $notification = Notification::deliver(
            correlationId: $intent->correlationId,
            type: $intent->notificationType,
            recipientEmployeeId: $intent->recipientEmployeeId,
            title: $intent->title,
            message: $intent->message,
            sourceContext: $intent->sourceContext,
            priority: $intent->priority,
            createdAt: $intent->occurredAt,
            entityReference: $intent->entityReference,
            deepLinkRoute: $intent->deepLinkRoute,
        );

        try {
            if (DB::transactionLevel() > 0) {
                DB::statement('SAVEPOINT notification_deliver');
            }

            $saved = $this->notifications->save($notification);
        } catch (QueryException $exception) {
            if (! $this->isDuplicateKeyViolation($exception)) {
                throw $exception;
            }

            if (DB::transactionLevel() > 0) {
                DB::statement('ROLLBACK TO SAVEPOINT notification_deliver');
            }

            $existing = $this->notifications->findByDedupKey(
                $intent->correlationId,
                $intent->recipientEmployeeId,
                $intent->notificationType,
            );

            if ($existing === null) {
                throw $exception;
            }

            return new NotificationDeliveryResultDto(
                notificationId: $existing->requireId()->value,
                status: DeliveryResultStatus::Duplicate,
            );
        }

        return new NotificationDeliveryResultDto(
            notificationId: $saved->requireId()->value,
            status: DeliveryResultStatus::Delivered,
        );
    }

    private function isDuplicateKeyViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;

        return $sqlState === '23505';
    }
}
