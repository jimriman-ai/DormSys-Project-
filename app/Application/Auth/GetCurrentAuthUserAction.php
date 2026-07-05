<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Auth\Contracts\ResolvesAuthUser;
use App\Domain\Auth\Data\AuthUserData;

class GetCurrentAuthUserAction
{
    public function __construct(
        private readonly ResolvesAuthUser $resolver,
    ) {}

    public function execute(): ?AuthUserData
    {
        return $this->resolver->current();
    }
}
