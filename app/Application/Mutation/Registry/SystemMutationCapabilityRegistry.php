<?php

declare(strict_types=1);

namespace App\Application\Mutation\Registry;

final class SystemMutationCapabilityRegistry
{
    /**
     * Foundation validation capability for system-actor enforcement tests only.
     */
    public const string FOUNDATION_SELF_TEST = 'mutation.system.foundation_self_test';

    /**
     * @return list<string>
     */
    public static function registeredKeys(): array
    {
        return [
            self::FOUNDATION_SELF_TEST,
        ];
    }

    public static function isRegistered(string $capabilityKey): bool
    {
        return in_array($capabilityKey, self::registeredKeys(), true);
    }
}
