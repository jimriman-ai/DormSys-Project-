<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Entities;

use App\Modules\Request\Domain\ValueObjects\RequestId;

final class MissionDetails
{
    public function __construct(
        public readonly RequestId $requestId,
        public readonly string $description,
        public readonly ?string $missionDocumentUrl,
    ) {}
}
