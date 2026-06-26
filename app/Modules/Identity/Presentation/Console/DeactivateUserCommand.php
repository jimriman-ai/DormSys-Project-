<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Console;

use App\Modules\Identity\Application\Services\DeactivateUserAction;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use Illuminate\Console\Command;

class DeactivateUserCommand extends Command
{
    protected $signature = 'identity:user-deactivate {user_id}';

    protected $description = 'Disable an Identity platform user account.';

    public function handle(DeactivateUserAction $action): int
    {
        $user = $action->execute(UserId::fromString((string) $this->argument('user_id')));

        $this->info('User deactivated: '.$user->requireId()->value);

        return self::SUCCESS;
    }
}
