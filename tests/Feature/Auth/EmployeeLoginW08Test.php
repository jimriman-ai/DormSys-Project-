<?php

declare(strict_types=1);

/**
 * W-08 F2 employee-auth-ui tests (Lead APPROVED WITH AMENDMENTS).
 *
 * W08-E-* = execution-only of existing suites (see report) — not modified here.
 * W08-B-03 = smoke only — no Spec04 Auth validation.
 */

use App\Models\User;
use App\Modules\Auth\Presentation\Livewire\EmployeeLogin;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Providers\AuthFoundationServiceProvider;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->app->register(AuthFoundationServiceProvider::class);
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1']);
});

function w08CredentialPair(
    string $email,
    string $password = 'secret-password',
    bool $withIdentity = true,
): void {
    User::factory()->create([
        'email' => $email,
        'password' => $password,
    ]);

    if (! $withIdentity) {
        return;
    }

    $identityUser = createIdentityUserThroughMutation('W-08 User', $email);
    assignRoleThroughMutation($identityUser->requireId(), IdentityRoleSeeder::ROLE_ADMINISTRATOR);
    UserModel::query()->findOrFail($identityUser->requireId()->value);
}

// --- W08-A Authentication behavior ---

it('W08-A-01 successful employee login binds api+identity and redirects', function (): void {
    w08CredentialPair('w08-a01@example.com');
    RateLimiter::clear('employee-login:127.0.0.1:w08-a01@example.com');

    Livewire::test(EmployeeLogin::class)
        ->set('identifier', 'w08-a01@example.com')
        ->set('password', 'secret-password')
        ->call('login')
        ->assertRedirect(route('requests.index'));

    expect(auth('api')->check())->toBeTrue()
        ->and(auth('identity')->check())->toBeTrue();
});

it('W08-A-02 invalid credentials show generic error and leave api/identity guest', function (): void {
    w08CredentialPair('w08-a02@example.com');
    RateLimiter::clear('employee-login:127.0.0.1:w08-a02@example.com');

    Livewire::test(EmployeeLogin::class)
        ->set('identifier', 'w08-a02@example.com')
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertSet('password', '')
        ->assertSet('errorMessage', 'اطلاعات ورود نامعتبر است.')
        ->assertNoRedirect();

    expect(auth('api')->check())->toBeFalse()
        ->and(auth('identity')->check())->toBeFalse();
});

it('W08-A-03 valid credentials without identity match leave api/identity guest', function (): void {
    w08CredentialPair('w08-a03@example.com', withIdentity: false);
    RateLimiter::clear('employee-login:127.0.0.1:w08-a03@example.com');

    Livewire::test(EmployeeLogin::class)
        ->set('identifier', 'w08-a03@example.com')
        ->set('password', 'secret-password')
        ->call('login')
        ->assertSet('password', '')
        ->assertSet('errorMessage', 'برای این حساب کاربری امکان ورود به سامانه وجود ندارد.')
        ->assertNoRedirect();

    expect(auth('api')->check())->toBeFalse()
        ->and(auth('identity')->check())->toBeFalse();
});

it('W08-A-04 guest can open employee.login', function (): void {
    $this->get(route('employee.login'))
        ->assertOk();
});

it('W08-A-05 successful login redirects to requests.index', function (): void {
    w08CredentialPair('w08-a05@example.com');
    RateLimiter::clear('employee-login:127.0.0.1:w08-a05@example.com');

    Livewire::test(EmployeeLogin::class)
        ->set('identifier', 'w08-a05@example.com')
        ->set('password', 'secret-password')
        ->call('login')
        ->assertRedirect(route('requests.index'));
});

// --- W08-B Authorization boundary ---

it('W08-B-01 authenticated api session can reach requests.index after EmployeeLogin', function (): void {
    w08CredentialPair('w08-b01@example.com');
    RateLimiter::clear('employee-login:127.0.0.1:w08-b01@example.com');

    Livewire::test(EmployeeLogin::class)
        ->set('identifier', 'w08-b01@example.com')
        ->set('password', 'secret-password')
        ->call('login');

    $this->get(route('requests.index'))->assertOk();
});

it('W08-B-02 unauthenticated guest is redirected away from requests.index', function (): void {
    $response = $this->get(route('requests.index'));

    expect($response->isRedirect())->toBeTrue();
});

it('W08-B-03 smoke: guest cannot open dormitory-admin manager (no Spec04 Auth validation)', function (): void {
    $response = $this->get(route('dormitory-admin.manager'));

    expect($response->isRedirect() || $response->status() === 403)->toBeTrue();
});

// --- W08-C F-W07-02 invariant (BLOCKING) ---

it('W08-C-01 Establish fail leaves api and identity unauthenticated', function (): void {
    w08CredentialPair('w08-c01@example.com', withIdentity: false);
    RateLimiter::clear('employee-login:127.0.0.1:w08-c01@example.com');

    Livewire::test(EmployeeLogin::class)
        ->set('identifier', 'w08-c01@example.com')
        ->set('password', 'secret-password')
        ->call('login');

    expect(auth('api')->check())->toBeFalse()
        ->and(auth('identity')->check())->toBeFalse();
});

it('W08-C-02 Establish fail clears default web auth and does not redirect to success', function (): void {
    w08CredentialPair('w08-c02@example.com', withIdentity: false);
    RateLimiter::clear('employee-login:127.0.0.1:w08-c02@example.com');

    Livewire::test(EmployeeLogin::class)
        ->set('identifier', 'w08-c02@example.com')
        ->set('password', 'secret-password')
        ->call('login')
        ->assertNoRedirect()
        ->assertSet('password', '');

    expect(auth('web')->check())->toBeFalse()
        ->and(auth('api')->check())->toBeFalse()
        ->and(auth('identity')->check())->toBeFalse();
});

it('W08-C-03 LoginUserAction failure never binds api or identity (C-5 adjacency)', function (): void {
    w08CredentialPair('w08-c03@example.com');
    RateLimiter::clear('employee-login:127.0.0.1:w08-c03@example.com');

    Livewire::test(EmployeeLogin::class)
        ->set('identifier', 'w08-c03@example.com')
        ->set('password', 'wrong-password')
        ->call('login');

    expect(auth('api')->check())->toBeFalse()
        ->and(auth('identity')->check())->toBeFalse()
        ->and(auth('web')->check())->toBeFalse();
});
