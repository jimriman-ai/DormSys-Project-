<?php

declare(strict_types=1);

use App\Modules\Dashboard\Presentation\Livewire\DashboardPage;
use Illuminate\Support\Facades\Route;

/**
 * WP-UI-C-DASH-01 — preserve named route `dashboard` coverage
 * (no prior DashboardRouteTest existed; this file owns that contract).
 */
it('registers the named dashboard route to DashboardPage behind auth:identity', function (): void {
    $route = Route::getRoutes()->getByName('dashboard');

    expect($route)->not->toBeNull();
    expect($route?->uri())->toBe('dashboard');
    expect($route?->getName())->toBe('dashboard');
    expect($route?->gatherMiddleware())->toContain('auth:identity');

    $action = (string) ($route?->getAction('controller')
        ?? $route?->getAction('uses')
        ?? $route?->getActionName());

    expect($action)->toContain(DashboardPage::class);
});
