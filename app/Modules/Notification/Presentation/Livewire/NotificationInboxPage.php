<?php

declare(strict_types=1);

namespace App\Modules\Notification\Presentation\Livewire;

use App\Modules\Notification\Application\Contracts\MarkNotificationReadContract;
use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;
use App\Modules\Notification\Application\DTOs\NotificationProjectionDto;
use App\Modules\Notification\Application\Services\NotificationPrincipalEmployeeResolver;
use App\Support\Presentation\Concerns\HandlesUiMutationFeedback;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Morilog\Jalali\Jalalian;
use Throwable;

#[Layout('components.layouts.app')]
final class NotificationInboxPage extends Component
{
    use HandlesUiMutationFeedback;

    private const LIST_LIMIT = 50;

    public string $uiState = 'loading';

    public ?string $loadError = null;

    /** @var list<array<string, mixed>> */
    public array $notifications = [];

    public function refreshList(
        NotificationInboxReadContract $inbox,
        NotificationPrincipalEmployeeResolver $principalEmployee,
    ): void {
        $this->uiState = 'loading';
        $this->loadError = null;

        try {
            $employeeId = $principalEmployee->requireEmployeeId();

            $this->notifications = array_map(
                static fn (NotificationProjectionDto $projection): array => self::mapProjectionRow($projection),
                $inbox->listForRecipient($employeeId, null, self::LIST_LIMIT),
            );

            $this->uiState = $this->notifications === [] ? 'empty' : 'ready';
        } catch (Throwable $exception) {
            $this->notifications = [];
            $this->loadError = $exception->getMessage();
            $this->uiState = 'error';
        }
    }

    public function markNotificationRead(
        string $notificationId,
        MarkNotificationReadContract $markRead,
        NotificationInboxReadContract $inbox,
        NotificationPrincipalEmployeeResolver $principalEmployee,
    ): void {
        $this->resetActionFeedback();

        try {
            $markRead->markRead(
                $notificationId,
                $principalEmployee->requireEmployeeId(),
                new DateTimeImmutable('now', new DateTimeZone('UTC')),
            );

            $this->flashSuccess('اعلان به‌عنوان خوانده‌شده علامت‌گذاری شد.');
            $this->refreshList($inbox, $principalEmployee);
        } catch (Throwable $exception) {
            $this->captureMutationFailure($exception);
        }
    }

    public function render(): View
    {
        return view('livewire.notification.notification-inbox-page');
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapProjectionRow(NotificationProjectionDto $projection): array
    {
        return [
            'id' => $projection->id,
            'notification_type' => $projection->notificationType,
            'title' => $projection->title,
            'message' => $projection->message,
            'is_read' => $projection->isRead,
            'read_at' => $projection->readAt === null
                ? null
                : (string) Jalalian::fromDateTime($projection->readAt)->format('Y/m/d H:i'),
            'created_at' => (string) Jalalian::fromDateTime($projection->createdAt)->format('Y/m/d H:i'),
            'priority' => $projection->priority,
        ];
    }
}
