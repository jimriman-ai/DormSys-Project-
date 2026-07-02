<?php

declare(strict_types=1);

namespace App\Modules\Notification\Infrastructure\Persistence\Models;

use App\Modules\Notification\Domain\Enums\DeliveryPriority;
use App\Modules\Notification\Domain\Enums\DeliveryStatus;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Support\Models\BaseModel;

/**
 * @property NotificationType $notification_type
 * @property DeliveryPriority $priority
 * @property DeliveryStatus $delivery_status
 */
class NotificationLogModel extends BaseModel
{
    protected $table = 'notification_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'correlation_id',
        'notification_type',
        'recipient_employee_id',
        'title',
        'message',
        'entity_type',
        'entity_id',
        'deep_link_route',
        'source_context',
        'priority',
        'read_at',
        'archived_at',
        'delivery_status',
        'skip_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'notification_type' => NotificationType::class,
            'priority' => DeliveryPriority::class,
            'delivery_status' => DeliveryStatus::class,
            'read_at' => 'datetime',
            'archived_at' => 'datetime',
        ]);
    }
}
