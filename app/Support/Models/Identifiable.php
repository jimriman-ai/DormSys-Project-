<?php

declare(strict_types=1);

namespace App\Support\Models;

/**
 * Contract for entities that expose a stable UUID identifier.
 */
interface Identifiable
{
    /**
     * Return the entity's unique identifier.
     */
    public function getId(): string;
}
