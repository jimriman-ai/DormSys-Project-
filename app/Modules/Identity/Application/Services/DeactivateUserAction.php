<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Domain\Entities\User;
use App\Modules\Identity\Domain\Events\UserDeactivated;
use App\Modules\Identity\Domain\Exceptions\CannotDeactivateLastAdministratorException;
use App\Modules\Identity\Domain\Exceptions\UserNotFoundException;
use App\Modules\Identity\Domain\PlatformRoles;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class DeactivateUserAction
{
    public function __construct(
        private readonly UserRepositoryContract $users,
        private readonly IdentityAuditEmitter $auditEmitter,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly IdentityMutationAuthorizationGate $identityMutationAuth,
    ) {}

    public function execute(UserId $userId): User
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::IDENTITY_USER_DEACTIVATE, [
            'userId' => $userId->value,
        ]);
        $this->identityMutationAuth->assertDeactivate();

        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new UserNotFoundException('User not found.');
        }

        if (
            $this->users->userHasRole($userId, PlatformRoles::SYSTEM_ADMINISTRATOR)
            && $this->users->countActiveSystemAdministrators() <= 1
        ) {
            throw new CannotDeactivateLastAdministratorException('Cannot deactivate the last active system administrator.');
        }

        return DB::transaction(function () use ($user): User {
            $user->disable();
            $persisted = $this->users->save($user);

            Event::dispatch(UserDeactivated::forUser($persisted->requireId()->value));

            $this->auditEmitter->recordUserDeactivated(
                $persisted->requireId(),
                IdentityAuditEmitter::occurredNow(),
            );

            return $persisted;
        });
    }
}
