<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;
use App\Modules\Request\Domain\ValueObjects\RequestCode;
use DateTimeImmutable;
use DateTimeZone;

final class RequestCodeGenerator
{
    public function __construct(
        private readonly RequestRepositoryContract $requests,
    ) {}

    public function generate(?DateTimeImmutable $at = null): RequestCode
    {
        $at ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $datePart = $at->format('Ymd');
        $sequence = $this->requests->nextDailySequenceForUtcDate($datePart);

        if ($sequence > 9999) {
            throw new RequestValidationException('Daily request code sequence exhausted.');
        }

        return RequestCode::fromString(sprintf('REQ-%s-%04d', $datePart, $sequence));
    }
}
