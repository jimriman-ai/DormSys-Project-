<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Auth\EstablishApiSessionFromCredentialLoginAction;
use App\Application\Auth\LoginUserAction;
use App\Application\Auth\LogoutUserAction;
use App\Domain\Auth\Data\AuthCredentialsData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AuthSessionController extends Controller
{
    public function __construct(
        private readonly LoginUserAction $loginUserAction,
        private readonly LogoutUserAction $logoutUserAction,
        private readonly EstablishApiSessionFromCredentialLoginAction $establishApiSession,
    ) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(ApiLoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $result = $this->loginUserAction->execute(new AuthCredentialsData(
            identifier: $validated['identifier'],
            password: $validated['password'],
        ));

        if (! $result->success) {
            return back()
                ->withInput($request->only('identifier'))
                ->with('error', 'اطلاعات ورود نامعتبر است.');
        }

        $email = $result->user?->identifier;
        assert(is_string($email));

        if (! $this->establishApiSession->execute($email)) {
            $this->logoutUserAction->execute();

            return back()
                ->withInput($request->only('identifier'))
                ->with('error', 'برای این حساب کاربری امکان ورود به سامانه وجود ندارد.');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('requests.index'));
    }

    public function destroy(): RedirectResponse
    {
        Auth::guard('api')->logout();
        $this->logoutUserAction->execute();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
