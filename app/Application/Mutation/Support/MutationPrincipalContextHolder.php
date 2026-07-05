<?php

declare(strict_types=1);

namespace App\Application\Mutation\Support;

final class MutationPrincipalContextHolder
{
    private ?string $actingPrincipalId = null;

    public function set(?string $actingPrincipalId): void
    {
        $this->actingPrincipalId = $actingPrincipalId;
    }

    public function get(): ?string
    {
        return $this->actingPrincipalId;
    }

    public function clear(): void
    {
        $this->actingPrincipalId = null;
    }
}
