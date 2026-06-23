<?php

declare(strict_types=1);

namespace App\Support\Contracts\Services;

/**
 * Base Service Interface
 *
 * Defines the contract for all service implementations in the application.
 * Services contain business logic and orchestrate operations across repositories.
 */
interface BaseServiceInterface
{
    /**
     * Handle the service operation.
     *
     * This method serves as the primary entry point for service execution.
     * Concrete implementations define specific parameters and return types.
     *
     * @param  mixed  ...$args  Variable arguments depending on service implementation
     * @return mixed Result of the service operation
     */
    public function handle(mixed ...$args): mixed;
}
