<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Identity\Application;

use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Application\Services\DeactivateUserAction;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Domain\Events\UserDeactivated;
use App\Modules\Identity\Domain\Exceptions\CannotDeactivateLastAdministratorException;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeactivateUserActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(IdentityRoleSeeder::class);
    }

    #[Test]
    public function it_disables_user_and_dispatches_user_deactivated(): void
    {
        Event::fake([UserDeactivated::class]);

        $created = app(CreateUserAction::class)->execute('Bob Example', 'bob@example.com');
        $deactivated = app(DeactivateUserAction::class)->execute($created->requireId());

        $this->assertSame(UserStatus::Disabled, $deactivated->status);
        $this->assertFalse($deactivated->isActive());

        Event::assertDispatched(UserDeactivated::class, function (UserDeactivated $event) use ($created): bool {
            return $event->aggregateId === $created->requireId()->value
                && $event->toContractPayload()['event'] === UserDeactivated::EVENT_NAME;
        });
    }

    #[Test]
    public function it_prevents_deactivating_last_active_system_administrator(): void
    {
        $admin = app(CreateUserAction::class)->execute('Admin User', 'admin@example.com');
        UserModel::query()->find($admin->requireId()->value)?->assignRole(IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR);

        $this->expectException(CannotDeactivateLastAdministratorException::class);

        app(DeactivateUserAction::class)->execute($admin->requireId());
    }

    #[Test]
    public function it_allows_deactivating_admin_when_another_active_admin_exists(): void
    {
        $firstAdmin = app(CreateUserAction::class)->execute('Admin One', 'admin1@example.com');
        UserModel::query()->find($firstAdmin->requireId()->value)?->assignRole(IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR);

        $secondAdmin = app(CreateUserAction::class)->execute('Admin Two', 'admin2@example.com');
        UserModel::query()->find($secondAdmin->requireId()->value)?->assignRole(IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR);

        $deactivated = app(DeactivateUserAction::class)->execute($firstAdmin->requireId());

        $this->assertSame(UserStatus::Disabled, $deactivated->status);
    }
}
