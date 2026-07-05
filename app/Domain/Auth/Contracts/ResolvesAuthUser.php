<?php

declare(strict_types=1);

namespace App\Domain\Auth\Contracts;

use App\Domain\Auth\Data\AuthUserData;

interface ResolvesAuthUser
{
    public function current(): ?AuthUserData;
}
