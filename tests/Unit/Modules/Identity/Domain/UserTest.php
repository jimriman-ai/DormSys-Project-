<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Identity\Domain;

use App\Modules\Identity\Domain\Entities\User;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Domain\Exceptions\InvalidUserStateTransitionException;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class UserTest extends TestCase
{
    #[Test]
    public function it_disables_an_active_user(): void
    {
        $user = $this->makeUser();

        $user->disable();

        $this->assertSame(UserStatus::Disabled, $user->status);
        $this->assertFalse($user->isActive());
    }

    #[Test]
    public function it_prevents_reactivation_in_wave_one_a(): void
    {
        $user = $this->makeUser();
        $user->disable();

        $this->expectException(InvalidUserStateTransitionException::class);

        $user->disable();
    }

    #[Test]
    public function it_preserves_immutable_identifier_after_assignment(): void
    {
        $user = $this->makeUser();
        $originalId = $user->requireId();

        $user->disable();

        $this->assertTrue($originalId->equals($user->requireId()));
    }

    private function makeUser(): User
    {
        return new User(
            id: UserId::fromString(Uuid::uuid7()->toString()),
            status: UserStatus::Active,
            displayName: 'Test User',
            email: 'test@example.com',
        );
    }
}
