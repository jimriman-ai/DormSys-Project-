<?php

declare(strict_types=1);

namespace App\Support\Development;

final readonly class DevelopmentUserAccountReport
{
    /**
     * @param  list<string>  $roles
     */
    public function __construct(
        public string $label,
        public string $email,
        public string $password,
        public bool $credentialCreated,
        public ?string $identityId,
        public bool $identityCreated,
        public ?string $employeeId,
        public bool $employeeCreated,
        public array $roles,
    ) {}
}
