<?php

declare(strict_types=1);

namespace App\Integrations\Notification;

use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Domain\Enums\DeliveryPriority;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Domain\ValueObjects\CorrelationId;
use App\Modules\Notification\Domain\ValueObjects\EntityReference;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Enums\ApprovalStage;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use DateTimeImmutable;

/**
 * Builds and delivers Request-approval notification intents (Notification owns persistence).
 * C1: Stage-1 concrete Identity→Employee only for pending / submitted next-approver.
 * D2: request_submitted → next approver (Stage-1), not requester.
 */
final class RequestApprovalNotificationDelivery
{
    private const string SOURCE_CONTEXT = 'request';

    private const string ENTITY_TYPE = 'request';

    private const string DEEP_LINK = 'requests.show';

    public function __construct(
        private readonly NotificationDeliveryContract $notifications,
        private readonly RequestRepositoryContract $requests,
        private readonly EmployeeRepositoryContract $employees,
    ) {}

    public function notifySubmittedToNextApprover(string $requestId, DateTimeImmutable $occurredAt): void
    {
        $approverEmployeeId = $this->resolveStage1ApproverEmployeeId($requestId);

        if ($approverEmployeeId === null) {
            return;
        }

        $this->notifications->deliver(new NotificationIntentDto(
            correlationId: CorrelationId::fromString(RequestApprovalNotificationCorrelation::submitted($requestId)),
            notificationType: NotificationType::RequestSubmitted,
            recipientEmployeeId: $approverEmployeeId,
            title: 'درخواست جدید برای تأیید',
            message: 'یک درخواست خوابگاه در انتظار تأیید شما است.',
            sourceContext: self::SOURCE_CONTEXT,
            priority: DeliveryPriority::Standard,
            occurredAt: $occurredAt,
            entityReference: EntityReference::fromStrings(self::ENTITY_TYPE, $requestId),
            deepLinkRoute: self::DEEP_LINK,
        ));
    }

    public function notifyPendingIfStage1(string $requestId, string $stage, DateTimeImmutable $occurredAt): void
    {
        if ($stage !== ApprovalStage::DepartmentManager->value) {
            return;
        }

        $approverEmployeeId = $this->resolveStage1ApproverEmployeeId($requestId);

        if ($approverEmployeeId === null) {
            return;
        }

        $this->notifications->deliver(new NotificationIntentDto(
            correlationId: CorrelationId::fromString(
                RequestApprovalNotificationCorrelation::pending($requestId, $stage),
            ),
            notificationType: NotificationType::RequestApprovalPending,
            recipientEmployeeId: $approverEmployeeId,
            title: 'تأیید مرحله اول در انتظار اقدام',
            message: 'درخواست در مرحله مدیر خوابگاه در انتظار تأیید شما است.',
            sourceContext: self::SOURCE_CONTEXT,
            priority: DeliveryPriority::Standard,
            occurredAt: $occurredAt,
            entityReference: EntityReference::fromStrings(self::ENTITY_TYPE, $requestId),
            deepLinkRoute: self::DEEP_LINK,
        ));
    }

    public function notifyApprovedToRequester(string $requestId, DateTimeImmutable $occurredAt): void
    {
        $requesterEmployeeId = $this->resolveRequesterEmployeeId($requestId);

        if ($requesterEmployeeId === null) {
            return;
        }

        $this->notifications->deliver(new NotificationIntentDto(
            correlationId: CorrelationId::fromString(RequestApprovalNotificationCorrelation::approved($requestId)),
            notificationType: NotificationType::RequestApproved,
            recipientEmployeeId: $requesterEmployeeId,
            title: 'درخواست خوابگاه تأیید شد',
            message: 'درخواست شما تأیید شد.',
            sourceContext: self::SOURCE_CONTEXT,
            priority: DeliveryPriority::Standard,
            occurredAt: $occurredAt,
            entityReference: EntityReference::fromStrings(self::ENTITY_TYPE, $requestId),
            deepLinkRoute: self::DEEP_LINK,
        ));
    }

    public function notifyRejectedToRequester(string $requestId, string $reason, DateTimeImmutable $occurredAt): void
    {
        $requesterEmployeeId = $this->resolveRequesterEmployeeId($requestId);

        if ($requesterEmployeeId === null) {
            return;
        }

        $message = $reason !== ''
            ? 'درخواست شما رد شد. دلیل: '.$reason
            : 'درخواست شما رد شد.';

        $this->notifications->deliver(new NotificationIntentDto(
            correlationId: CorrelationId::fromString(RequestApprovalNotificationCorrelation::rejected($requestId)),
            notificationType: NotificationType::RequestRejected,
            recipientEmployeeId: $requesterEmployeeId,
            title: 'درخواست خوابگاه رد شد',
            message: $message,
            sourceContext: self::SOURCE_CONTEXT,
            priority: DeliveryPriority::Standard,
            occurredAt: $occurredAt,
            entityReference: EntityReference::fromStrings(self::ENTITY_TYPE, $requestId),
            deepLinkRoute: self::DEEP_LINK,
        ));
    }

    private function resolveStage1ApproverEmployeeId(string $requestId): ?string
    {
        $request = $this->requests->findById(RequestId::fromString($requestId));

        if ($request === null) {
            return null;
        }

        $identityId = $request->assignedStage1ApproverIdentityId;

        if ($identityId === null || $identityId === '') {
            return null;
        }

        return $this->employees->findEmployeeIdByIdentityUserId($identityId);
    }

    private function resolveRequesterEmployeeId(string $requestId): ?string
    {
        $request = $this->requests->findById(RequestId::fromString($requestId));

        return $request?->employeeId->value;
    }
}
