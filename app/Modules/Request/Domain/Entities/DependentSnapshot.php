<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Entities;

use App\Modules\Request\Domain\Enums\DependentRelationship;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use DateTimeImmutable;

final class DependentSnapshot
{
    public function __construct(
        public readonly ?string $id,
        public readonly RequestId $requestId,
        public readonly ?string $sourceDependentId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly DependentRelationship $relationship,
        public readonly ?string $nationalCode,
        public readonly DateTimeImmutable $capturedAt,
    ) {}
}
