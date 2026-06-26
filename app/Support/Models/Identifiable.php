<?php

declare(strict_types=1);

namespace App\Support\Models;

/**
 * Contract for entities that expose a stable UUID identifier.
 */
interface Identifiable
{
    /**
     * Return the entity's unique identifier after its UUID has been assigned.
     *
     * Assignment may occur on the creating event before the record is persisted.
     *
     * @throws \LogicException when the UUID has not yet been assigned
     */
    public function getId(): string;
}
