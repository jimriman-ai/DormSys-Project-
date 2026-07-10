<?php

declare(strict_types=1);

namespace App\Modules\Notification\Presentation\Livewire;

use App\Modules\Notification\Application\Contracts\MarkNotificationReadContract;
use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;
use App\Modules\Notification\Application\DTOs\NotificationInboxListQueryDTO;
use App\Modules\Notification\Application\DTOs\NotificationProjectionDto;
use App\Modules\Notification\Application\Services\NotificationPrincipalEmployeeResolver;
use App\Support\Presentation\Concerns\HandlesUiMutationFeedback;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Morilog\Jalali\Jalalian;
use Throwable;

#[Layout('components.layouts.app')]
final class NotificationInboxPage extends Component
{
    use HandlesUiMutationFeedback;

    private const int PER_PAGE = 50;

    private const APPROVED_REQUEST_SHOW_ROUTE = 'requests.show';

    public string $uiState = 'loading';

    public ?string $loadError = null;

    #[Url(as: 'page', except: 1)]
    public int $page = 1;

    public int $total = 0;

    public int $lastPage = 1;

    public int $perPage = self::PER_PAGE;

    /** @var list<array<string, mixed>> */
    public array $notifications = [];

    public function updatedPage(
        NotificationInboxReadContract $inbox,
        NotificationPrincipalEmployeeResolver $principalEmployee,
    ): void {
        $this->refreshList($inbox, $principalEmployee);
    }

    public function goToPage(
        int $page,
        NotificationInboxReadContract $inbox,
        NotificationPrincipalEmployeeResolver $principalEmployee,
    ): void {
        $this->page = max($page, 1);
        $this->refreshList($inbox, $principalEmployee);
    }

    public function refreshList(
        NotificationInboxReadContract $inbox,
        NotificationPrincipalEmployeeResolver $principalEmployee,
    ): void {
        $this->uiState = 'loading';
        $this->loadError = null;

        try {
            $employeeId = $principalEmployee->requireEmployeeId();

            if ($this->page < 1) {
                $this->page = 1;
            }

            $result = $inbox->listForRecipientPaginated(new NotificationInboxListQueryDTO(
                recipientEmployeeId: $employeeId,
                unreadOnly: null,
                page: $this->page,
                perPage: self::PER_PAGE,
            ));

            $this->page = $result->currentPage;
            $this->total = $result->total;
            $this->lastPage = $result->lastPage;
            $this->perPage = $result->perPage;

            $this->notifications = array_map(
                static fn (NotificationProjectionDto $projection): array => self::mapProjectionRow($projection),
                $result->items,
            );

            $this->uiState = $this->total === 0 ? 'empty' : 'ready';
        } catch (Throwable $exception) {
            $this->notifications = [];
            $this->total = 0;
            $this->lastPage = 1;
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
        $requestShowUrl = null;

        if (
            $projection->deepLinkRoute === self::APPROVED_REQUEST_SHOW_ROUTE
            && $projection->entityId !== null
        ) {
            $requestShowUrl = route(self::APPROVED_REQUEST_SHOW_ROUTE, ['requestId' => $projection->entityId]);
        }

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
            'request_show_url' => $requestShowUrl,
        ];
    }
}
