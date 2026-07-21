<?php

declare(strict_types=1);

namespace App\Integrations\Notification;

/**
 * Stable correlation keys for WP-WF-05 dual-source dedup (STOP-B B3).
 * Same key + recipient + type ⇒ NotificationDeliveryContract returns Duplicate.
 */
final class RequestApprovalNotificationCorrelation
{
    public static function submitted(string $requestId): string
    {
        return 'request:'.$requestId.':submitted';
    }

    public static function pending(string $requestId, string $stage): string
    {
        return 'request:'.$requestId.':pending:'.$stage;
    }

    public static function approved(string $requestId): string
    {
        return 'request:'.$requestId.':approved';
    }

    public static function rejected(string $requestId): string
    {
        return 'request:'.$requestId.':rejected';
    }
}
