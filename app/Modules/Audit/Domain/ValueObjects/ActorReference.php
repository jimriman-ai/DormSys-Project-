<?php

declare(strict_types=1);

namespace App\Modules\Audit\Domain\ValueObjects;

use App\Modules\Audit\Domain\Enums\ActorType;
use App\Support\Exceptions\ValidationException;

final readonly class ActorReference
{
    public function __construct(
        public ActorType $actorType,
        public string $actorId,
    ) {
        if ($actorId === '' || strlen($actorId) > 128) {
            throw new ValidationException('Actor identifier is required.');
        }
    }

    public static function user(string $userId): self
    {
        return new self(ActorType::User, $userId);
    }

    public static function system(string $token): self
    {
        return new self(ActorType::System, $token);
    }
}
