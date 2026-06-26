<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Identity\Application;

use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Domain\Events\UserCreated;
use App\Modules\Identity\Domain\Exceptions\DuplicateUserEmailException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CreateUserActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_assigns_uuid_v7_and_dispatches_user_created(): void
    {
        Event::fake([UserCreated::class]);

        $user = app(CreateUserAction::class)->execute('Alice Example', 'alice@example.com');

        $userId = $user->requireId();

        $this->assertTrue(Uuid::isValid($userId->value));
        $this->assertSame(7, Uuid::fromString($userId->value)->getVersion());
        $this->assertTrue($user->isActive());

        Event::assertDispatched(UserCreated::class, function (UserCreated $event) use ($userId): bool {
            return $event->aggregateId === $userId->value
                && $event->toContractPayload()['event'] === UserCreated::EVENT_NAME;
        });
    }

    #[Test]
    public function it_rejects_duplicate_email(): void
    {
        $action = app(CreateUserAction::class);
        $action->execute('First User', 'dup@example.com');

        $this->expectException(DuplicateUserEmailException::class);

        $action->execute('Second User', 'dup@example.com');
    }
}
