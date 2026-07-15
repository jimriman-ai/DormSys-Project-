<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Livewire;

use App\Application\Auth\EstablishApiSessionFromCredentialLoginAction;
use App\Application\Auth\LoginUserAction;
use App\Application\Auth\LogoutUserAction;
use App\Domain\Auth\Data\AuthCredentialsData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * F2 / W-01 employee-auth-ui login surface.
 *
 * C-5: EstablishApiSessionFromCredentialLoginAction runs only after successful LoginUserAction.
 * F-W07-01: RateLimiter gates attempts before LoginUserAction (key employee-login:{ip}:{email}).
 */
#[Layout('components.layouts.guest')]
final class EmployeeLogin extends Component
{
    private const int MAX_ATTEMPTS = 5;

    private const int DECAY_SECONDS = 60;

    public string $identifier = '';

    public string $password = '';

    public ?string $errorMessage = null;

    public function login(
        LoginUserAction $loginUserAction,
        EstablishApiSessionFromCredentialLoginAction $establishApiSession,
        LogoutUserAction $logoutUserAction,
    ): void {
        $this->errorMessage = null;

        $this->validate([
            'identifier' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $rateLimitKey = $this->rateLimitKey();

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::MAX_ATTEMPTS)) {
            $this->errorMessage = 'تلاش‌های ورود بیش از حد مجاز است. لطفاً کمی بعد دوباره تلاش کنید.';
            $this->password = '';

            return;
        }

        $result = $loginUserAction->execute(new AuthCredentialsData(
            identifier: $this->identifier,
            password: $this->password,
        ));

        if (! $result->success) {
            RateLimiter::hit($rateLimitKey, self::DECAY_SECONDS);
            $this->errorMessage = 'اطلاعات ورود نامعتبر است.';
            $this->password = '';

            return;
        }

        $email = $result->user?->identifier;
        assert(is_string($email));

        if (! $establishApiSession->execute($email)) {
            RateLimiter::hit($rateLimitKey, self::DECAY_SECONDS);
            $logoutUserAction->execute();
            $this->errorMessage = 'برای این حساب کاربری امکان ورود به سامانه وجود ندارد.';
            $this->password = '';

            return;
        }

        RateLimiter::clear($rateLimitKey);

        session()->regenerate();

        $this->redirectIntended(route('requests.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.employee-login');
    }

    private function rateLimitKey(): string
    {
        $ip = request()->ip() ?? 'unknown';
        $email = Str::lower(trim($this->identifier));

        return 'employee-login:'.$ip.':'.$email;
    }
}
