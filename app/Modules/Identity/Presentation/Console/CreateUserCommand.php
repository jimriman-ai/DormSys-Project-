<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Console;

use App\Modules\Identity\Application\Services\CreateUserAction;
use Illuminate\Console\Command;

class CreateUserCommand extends Command
{
    protected $signature = 'identity:user-create {display_name} {--email=}';

    protected $description = 'Create an Identity platform user account (no authentication gateway).';

    public function handle(CreateUserAction $action): int
    {
        $user = $action->execute(
            displayName: (string) $this->argument('display_name'),
            email: $this->option('email') !== null ? (string) $this->option('email') : null,
        );

        $this->info('User created: '.$user->requireId()->value);

        return self::SUCCESS;
    }
}
