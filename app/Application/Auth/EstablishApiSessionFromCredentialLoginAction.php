<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use Illuminate\Support\Facades\Auth;

final class EstablishApiSessionFromCredentialLoginAction
{
    public function __construct(
        private readonly UserRepositoryContract $userRepository,
    ) {}

    public function execute(string $email): bool
    {
        $identityUser = $this->userRepository->findByEmail($email);

        if ($identityUser === null || ! $identityUser->isActive()) {
            return false;
        }

        Auth::guard('api')->loginUsingId($identityUser->requireId()->value);
        Auth::guard('identity')->loginUsingId($identityUser->requireId()->value);

        return true;
    }
}
