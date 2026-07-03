<?php

declare(strict_types=1);

namespace App\Modules\Notification\Infrastructure\Jobs;

use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Domain\Enums\DeliveryPriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SendNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $intentPayload
     */
    public function __construct(
        private readonly array $intentPayload,
    ) {
        $priority = DeliveryPriority::from((string) ($intentPayload['priority'] ?? DeliveryPriority::Standard->value));
        $this->onQueue($priority === DeliveryPriority::Urgent ? 'notifications-urgent' : 'notifications');
    }

    public function handle(NotificationDeliveryContract $delivery): void
    {
        $delivery->deliver(NotificationIntentDto::fromArray($this->intentPayload));
    }

    /**
     * @return array<string, mixed>
     */
    public function intentPayload(): array
    {
        return $this->intentPayload;
    }
}
