<?php

declare(strict_types=1);

namespace App\Integrations\Notification;

use App\Modules\Request\Domain\Events\RequestApproved;
use App\Modules\Request\Domain\Events\RequestRejected;
use App\Modules\Request\Domain\Events\RequestSubmitted;
use App\Modules\Workflow\Domain\Events\WorkflowInstanceCompleted;
use App\Modules\Workflow\Domain\Events\WorkflowInstanceRejected;
use App\Modules\Workflow\Domain\Events\WorkflowStepActivated;

/**
 * WP-WF-05 — dual-source listeners (B3) with NotificationDeliveryContract dedup.
 * Does not own RequestApproval history or Workflow domain.
 */
final class RequestApprovalNotificationSubscriber
{
    public function __construct(
        private readonly RequestApprovalNotificationDelivery $delivery,
    ) {}

    public function onRequestSubmitted(RequestSubmitted $event): void
    {
        $requestId = (string) ($event->payload['request_id'] ?? $event->aggregateId);
        $this->delivery->notifySubmittedToNextApprover($requestId, $event->occurredAt);
    }

    public function onWorkflowStepActivated(WorkflowStepActivated $event): void
    {
        $requestId = (string) ($event->payload['request_id'] ?? '');
        $stage = (string) ($event->payload['stage'] ?? '');

        if ($requestId === '' || $stage === '') {
            return;
        }

        $this->delivery->notifyPendingIfStage1($requestId, $stage, $event->occurredAt);
    }

    public function onRequestApproved(RequestApproved $event): void
    {
        $requestId = (string) ($event->payload['request_id'] ?? $event->aggregateId);
        $this->delivery->notifyApprovedToRequester($requestId, $event->occurredAt);
    }

    public function onWorkflowInstanceCompleted(WorkflowInstanceCompleted $event): void
    {
        $requestId = (string) ($event->payload['request_id'] ?? '');

        if ($requestId === '') {
            return;
        }

        $this->delivery->notifyApprovedToRequester($requestId, $event->occurredAt);
    }

    public function onRequestRejected(RequestRejected $event): void
    {
        $requestId = (string) ($event->payload['request_id'] ?? $event->aggregateId);
        $reason = (string) ($event->payload['reason'] ?? '');
        $this->delivery->notifyRejectedToRequester($requestId, $reason, $event->occurredAt);
    }

    public function onWorkflowInstanceRejected(WorkflowInstanceRejected $event): void
    {
        $requestId = (string) ($event->payload['request_id'] ?? '');
        $reason = (string) ($event->payload['reason'] ?? '');

        if ($requestId === '') {
            return;
        }

        $this->delivery->notifyRejectedToRequester($requestId, $reason, $event->occurredAt);
    }
}
