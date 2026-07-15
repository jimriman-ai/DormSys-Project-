<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Auth\EstablishApiSessionFromCredentialLoginAction;
use App\Application\Auth\LoginUserAction;
use App\Application\Auth\LogoutUserAction;
use App\Domain\Auth\Data\AuthCredentialsData;
use App\Http\Requests\ApiLoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class ApiAuthSessionController extends Controller
{
    public function __construct(
        private readonly LoginUserAction $loginUserAction,
        private readonly LogoutUserAction $logoutUserAction,
        private readonly EstablishApiSessionFromCredentialLoginAction $establishApiSession,
    ) {}

    public function login(ApiLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->loginUserAction->execute(new AuthCredentialsData(
            identifier: $validated['identifier'],
            password: $validated['password'],
        ));

        if (! $result->success) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
                'failureReason' => $result->failureReason,
            ], Response::HTTP_UNAUTHORIZED);
        }

        $email = $result->user?->identifier;
        assert(is_string($email));

        if (! $this->establishApiSession->execute($email)) {
            $this->logoutUserAction->execute();

            return response()->json([
                'success' => false,
                'message' => 'No API principal available for this account.',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();
        Auth::guard('identity')->logout();
        $this->logoutUserAction->execute();

        return response()->json([
            'success' => true,
        ]);
    }
}
