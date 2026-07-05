<?php

declare(strict_types=1);

namespace App\Application\Mutation\Contracts;

interface MutationPrincipalContextPort
{
    public function currentPrincipalId(): ?string;
}
