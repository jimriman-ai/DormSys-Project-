<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\DTOs;

use DateTimeImmutable;

final readonly class EligibilityResultDTO
{
    /**
     * @param  list<string>  $reasonCodes
     */
    public function __construct(
        public bool $eligible,
        public array $reasonCodes,
        public DateTimeImmutable $evaluatedAt,
    ) {}
}
