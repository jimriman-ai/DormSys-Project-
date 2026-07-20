<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Presentation\View\Composers;

use App\Shared\Auth\IdentityRoleGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;

/**
 * WP-UI-C-DASH-02 — role-aware dashboard sidebar nav (identity guard only).
 */
final class DashboardNavComposer
{
    public function compose(View $view): void
    {
        $user = auth('identity')->user();

        $view->with('dashboard_nav_items', $this->itemsFor($user));
    }

    /**
     * @return list<array{key: string, label: string, url: string, active: bool}>
     */
    private function itemsFor(?Authenticatable $user): array
    {
        if ($user === null) {
            return [];
        }

        $isEmployee = IdentityRoleGuard::userHasIdentityRole($user, IdentityRoleGuard::ROLE_EMPLOYEE);
        $isManager = IdentityRoleGuard::userHasIdentityRole($user, IdentityRoleGuard::ROLE_DORMITORY_MANAGER);

        if (! $isEmployee && ! $isManager) {
            return [];
        }

        $items = [
            [
                'key' => 'dashboard',
                'label' => 'داشبورد',
                'url' => route('dashboard'),
                'active' => request()->routeIs('dashboard'),
            ],
            [
                'key' => 'requests',
                'label' => 'درخواست‌ها',
                'url' => route('requests.index'),
                'active' => request()->routeIs('requests.*'),
            ],
        ];

        // WP-DASH-G03-R1 / Q-G03-MGR-PATH — employee-only; managers keep dormitory-admin.
        if ($isEmployee) {
            $items[] = [
                'key' => 'dormitories',
                'label' => 'خوابگاه‌ها',
                'url' => route('dormitories.index'),
                'active' => request()->routeIs('dormitories.*'),
            ];
        }

        if ($isManager) {
            $items[] = [
                'key' => 'approvals-stage1',
                'label' => 'تأیید مرحله ۱',
                'url' => route('approvals.stage1.index'),
                'active' => request()->routeIs('approvals.stage1.*'),
            ];
        }

        return $items;
    }
}
