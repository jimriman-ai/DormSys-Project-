<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Exceptions;

final class RequestNotEligibleException extends RequestDomainException
{
    /**
     * @param  list<string>  $reasonCodes
     */
    public function __construct(
        string $message = 'Request is not eligible for submission.',
        public readonly array $reasonCodes = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
