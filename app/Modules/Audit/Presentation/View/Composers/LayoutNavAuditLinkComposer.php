<?php

declare(strict_types=1);

namespace App\Modules\Audit\Presentation\View\Composers;

use App\Modules\Audit\Application\Contracts\AuditPermissionReadPort;
use Illuminate\View\View;

final class LayoutNavAuditLinkComposer
{
    public function __construct(
        private readonly AuditPermissionReadPort $permissionRead,
    ) {}

    public function compose(View $view): void
    {
        $principalId = request()->attributes->get('audit_principal_user_id');

        $showAuditNav = is_string($principalId)
            && $principalId !== ''
            && $this->permissionRead->principalHasAuditReadPermission($principalId);

        $view->with('show_audit_nav', $showAuditNav);
    }
}
