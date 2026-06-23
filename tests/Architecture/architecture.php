<?php

declare(strict_types=1);

/**
 * @return list<string>
 */
function architectureModuleNames(): array
{
    return [
        'Identity',
        'Employee',
        'Request',
        'Workflow',
        'Dormitory',
        'Allocation',
        'Lottery',
        'Voucher',
        'Notification',
        'Audit',
        'Reporting',
    ];
}

/**
 * @return list<string>
 */
function architectureSharedNamespaces(): array
{
    return [
        'App\Support',
        'App\Shared',
    ];
}

/**
 * @return list<string>
 */
function architectureForeignModuleNamespaces(string $module): array
{
    return array_values(array_map(
        static fn (string $name): string => "App\\Modules\\{$name}",
        array_filter(
            architectureModuleNames(),
            static fn (string $name): bool => $name !== $module
        )
    ));
}

/**
 * @return list<string>
 */
function architectureModuleServiceProviders(): array
{
    return array_map(
        static fn (string $module): string => "App\\Modules\\{$module}\\Infrastructure\\Providers\\{$module}ServiceProvider",
        architectureModuleNames()
    );
}
