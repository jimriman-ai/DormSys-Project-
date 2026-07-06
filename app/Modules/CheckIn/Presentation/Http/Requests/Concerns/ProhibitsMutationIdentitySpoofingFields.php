<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Presentation\Http\Requests\Concerns;

trait ProhibitsMutationIdentitySpoofingFields
{
    /**
     * @return array<string, mixed>
     */
    protected function identitySpoofingProhibitionRules(): array
    {
        return [
            'approverId' => ['prohibited'],
            'approver_id' => ['prohibited'],
            'actorId' => ['prohibited'],
            'actor_id' => ['prohibited'],
            'operatorId' => ['prohibited'],
            'operator_id' => ['prohibited'],
            'ownerId' => ['prohibited'],
            'owner_id' => ['prohibited'],
            'principalId' => ['prohibited'],
            'principal_id' => ['prohibited'],
            'audit_principal_user_id' => ['prohibited'],
        ];
    }
}
