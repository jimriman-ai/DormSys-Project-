<?php

declare(strict_types=1);

namespace App\Modules\Notification\Presentation\View\Composers;

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;
use App\Modules\Notification\Application\Services\NotificationPrincipalEmployeeResolver;
use Illuminate\View\View;

final class LayoutNavUnreadBadgeComposer
{
    public function __construct(
        private readonly NotificationPrincipalEmployeeResolver $employeeResolver,
        private readonly NotificationInboxReadContract $inboxRead,
    ) {}

    public function compose(View $view): void
    {
        $view->with('show_badge', false);

        try {
            $employeeId = $this->employeeResolver->requireEmployeeId();
        } catch (UnauthorizedMutationException) {
            return;
        }

        $unreadCount = $this->inboxRead->countUnread($employeeId);

        if ($unreadCount <= 0) {
            return;
        }

        $view->with([
            'show_badge' => true,
            'unread_count' => $unreadCount,
        ]);
    }
}
