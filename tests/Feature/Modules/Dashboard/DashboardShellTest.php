<?php

declare(strict_types=1);

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

function createDashboardShellIdentityUser(string $displayName = 'Dashboard Shell User'): UserModel
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => $displayName,
        'email' => 'dash.shell.'.uniqid('', true).'@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return UserModel::query()->findOrFail($id);
}

it('redirects guests from /dashboard to login', function (): void {
    $this->get('/dashboard')
        ->assertRedirect('/login');
});

it('renders the dashboard shell for an authenticated identity user', function (): void {
    $user = createDashboardShellIdentityUser();

    $this->actingAs($user, 'identity')
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-testid="dashboard-shell"', false)
        ->assertSee('data-testid="dashboard-heading"', false)
        ->assertSee('Dashboard', false);
});

it('does not redirect /dashboard to /requests', function (): void {
    $user = createDashboardShellIdentityUser();

    $response = $this->actingAs($user, 'identity')
        ->get('/dashboard');

    $response->assertOk();
    expect($response->headers->get('Location'))->toBeNull();
});
