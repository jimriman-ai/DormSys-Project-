<?php

declare(strict_types=1);

namespace App\Modules\Identity\Domain\Entities;

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Domain\Exceptions\InvalidUserStateTransitionException;
use App\Modules\Identity\Domain\ValueObjects\UserId;

final class User
{
    public function __construct(
        public readonly ?UserId $id,
        public UserStatus $status,
        public string $displayName,
        public ?string $email,
    ) {}

    public static function createNew(string $displayName, ?string $email): self
    {
        return new self(
            id: null,
            status: UserStatus::Active,
            displayName: $displayName,
            email: $email,
        );
    }

    public function assignId(UserId $id): self
    {
        return new self(
            id: $id,
            status: $this->status,
            displayName: $this->displayName,
            email: $this->email,
        );
    }

    public function disable(): void
    {
        if ($this->status === UserStatus::Disabled) {
            throw new InvalidUserStateTransitionException('User is already disabled.');
        }

        $this->status = UserStatus::Disabled;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function requireId(): UserId
    {
        if ($this->id === null) {
            throw new \LogicException('User identifier is not assigned.');
        }

        return $this->id;
    }
}
