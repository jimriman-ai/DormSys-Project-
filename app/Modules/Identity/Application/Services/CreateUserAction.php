<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Domain\Entities\User;
use App\Modules\Identity\Domain\Events\UserCreated;
use App\Modules\Identity\Domain\Exceptions\DuplicateUserEmailException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class CreateUserAction
{
    public function __construct(
        private readonly UserRepositoryContract $users,
    ) {}

    public function execute(string $displayName, ?string $email = null): User
    {
        if ($email !== null && $this->users->existsByEmail($email)) {
            throw new DuplicateUserEmailException('A user with this email already exists.');
        }

        return DB::transaction(function () use ($displayName, $email): User {
            $user = User::createNew($displayName, $email);
            $persisted = $this->users->save($user);

            Event::dispatch(UserCreated::forUser($persisted->requireId()->value));

            return $persisted;
        });
    }
}
