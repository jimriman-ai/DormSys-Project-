<?php

declare(strict_types=1);

use App\Application\Auth\LoginUserAction;
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
    RateLimiter::clear(employeeLoginRateLimitKey());
});

function employeeLoginRateLimitKey(string $email = 'fw07-throttle@example.com'): string
{
    return 'employee-login:127.0.0.1:'.$email;
}

function createEmployeeLoginCredentialPair(
    string $email = 'fw07-throttle@example.com',
    string $password = 'secret-password',
): void {
    User::factory()->create([
        'email' => $email,
        'password' => $password,
    ]);

    $identityUser = createIdentityUserThroughMutation('F-W07 Throttle User', $email);
    assignRoleThroughMutation($identityUser->requireId(), IdentityRoleSeeder::ROLE_ADMINISTRATOR);
    UserModel::query()->findOrFail($identityUser->requireId()->value);
}

it('allows login within the rate limit and clears the limiter on success', function (): void {
    createEmployeeLoginCredentialPair();

    Livewire::test(EmployeeLogin::class)
        ->set('identifier', 'fw07-throttle@example.com')
        ->set('password', 'secret-password')
        ->call('login')
        ->assertRedirect(route('requests.index'));

    expect((int) RateLimiter::attempts(employeeLoginRateLimitKey()))->toBe(0)
        ->and(auth('api')->check())->toBeTrue()
        ->and(auth('identity')->check())->toBeTrue();
});

it('increments the limiter and clears password on invalid credentials', function (): void {
    createEmployeeLoginCredentialPair();

    Livewire::test(EmployeeLogin::class)
        ->set('identifier', 'fw07-throttle@example.com')
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertSet('password', '')
        ->assertSet('errorMessage', 'اطلاعات ورود نامعتبر است.');

    expect((int) RateLimiter::attempts(employeeLoginRateLimitKey()))->toBe(1)
        ->and(auth('api')->check())->toBeFalse();
});

it('blocks further attempts without calling LoginUserAction when limited', function (): void {
    createEmployeeLoginCredentialPair();

    $key = employeeLoginRateLimitKey();
    foreach (range(1, 5) as $_) {
        RateLimiter::hit($key, 60);
    }

    $this->mock(LoginUserAction::class, function ($mock): void {
        $mock->shouldNotReceive('execute');
    });

    Livewire::test(EmployeeLogin::class)
        ->set('identifier', 'FW07-Throttle@Example.com')
        ->set('password', 'secret-password')
        ->call('login')
        ->assertSet('password', '')
        ->assertSet('errorMessage', 'تلاش‌های ورود بیش از حد مجاز است. لطفاً کمی بعد دوباره تلاش کنید.');

    expect(auth('api')->check())->toBeFalse()
        ->and(auth('identity')->check())->toBeFalse();
});

it('normalizes email case in the rate-limit key', function (): void {
    createEmployeeLoginCredentialPair();

    Livewire::test(EmployeeLogin::class)
        ->set('identifier', 'FW07-Throttle@Example.com')
        ->set('password', 'wrong-password')
        ->call('login');

    expect((int) RateLimiter::attempts(employeeLoginRateLimitKey()))->toBe(1);
});
