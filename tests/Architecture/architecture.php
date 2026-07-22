<?php

declare(strict_types=1);

/**
 * Modules with full matrix enforcement (layer + boundary rules).
 *
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
        'CheckIn',
        'Lottery',
        'Voucher',
        'Notification',
        'Audit',
        'Reporting',
    ];
}

/**
 * Active modules registered in bootstrap but intentionally excluded from the full
 * matrix until documented contract debt is resolved. Do not add here to bypass CI.
 *
 * @return list<string>
 */
function architectureMatrixExcludedActiveModules(): array
{
    return [];
}

/**
 * Cross-module ports that MUST bind only in IntegrationServiceProvider.
 *
 * @return list<class-string>
 */
function architectureIntegrationPortClasses(): array
{
    return [
        'App\\Modules\\Allocation\\Application\\Contracts\\Ports\\ApprovedRequestReadPort',
        'App\\Modules\\Allocation\\Application\\Contracts\\Ports\\DormitoryReadPort',
        'App\\Modules\\Allocation\\Application\\Contracts\\Ports\\PhysicalStateSignalPort',
        'App\\Modules\\Allocation\\Application\\Contracts\\RequestLifecycleCommandPort',
        'App\\Modules\\CheckIn\\Application\\Contracts\\AllocationAssignmentReadPort',
        'App\\Modules\\Request\\Application\\Contracts\\Internal\\RequestEligibilityGatewayContract',
        'App\\Modules\\Request\\Application\\Contracts\\DormitoryReadContract',
        'App\\Modules\\Employee\\Application\\Contracts\\Ports\\PendingRequestReadPort',
        'App\\Modules\\Lottery\\Application\\Contracts\\ProposedAllocationPort',
        'App\\Modules\\Audit\\Application\\Contracts\\AuditPermissionReadPort',
        'App\\Modules\\Reporting\\Application\\Contracts\\Ports\\AuditHistorySourceReadPort',
        'App\\Modules\\Reporting\\Application\\Contracts\\Ports\\ReportingArchiveVisibilityPort',
    ];
}

/**
 * Legacy cross-module port bindings allowed outside the composition root until migrated.
 *
 * @return array<class-string, string> port FQCN => relative provider path from repo root
 */
function architectureLegacyModuleProviderPortBindings(): array
{
    return [
        'App\\Modules\\Lottery\\Application\\Contracts\\LotteryRequestReadPort' => 'app/Modules/Lottery/Infrastructure/Providers/LotteryServiceProvider.php',
    ];
}

/**
 * Cross-module adapters outside app/Integrations that remain tolerated until migrated.
 * New adapters MUST NOT be added here without architecture approval.
 *
 * @return list<string> repo-relative file paths
 */
function architectureLegacyCrossModuleAdapterPaths(): array
{
    return [
        'app/Modules/Lottery/Application/Adapters/RequestReadAdapter.php',
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
 * @return list<class-string>
 */
function architectureModuleServiceProviders(): array
{
    return array_values(array_map(
        static function (string $module): string {
            /** @var class-string $providerClass */
            $providerClass = "App\\Modules\\{$module}\\Infrastructure\\Providers\\{$module}ServiceProvider";

            return $providerClass;
        },
        architectureModuleNames()
    ));
}

/**
 * Discover module names that expose an Infrastructure service provider on disk.
 *
 * @return list<string>
 */
function architectureDiscoverActiveModuleNames(): array
{
    $modulesPath = app_path('Modules');

    if (! is_dir($modulesPath)) {
        return [];
    }

    $modules = [];

    foreach (scandir($modulesPath) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $providerPath = "{$modulesPath}/{$entry}/Infrastructure/Providers/{$entry}ServiceProvider.php";

        if (is_file($providerPath)) {
            $modules[] = $entry;
        }
    }

    sort($modules);

    return $modules;
}

/**
 * Module Infrastructure service providers registered in bootstrap/providers.php.
 *
 * @return list<class-string>
 */
function architectureBootstrapModuleServiceProviders(): array
{
    /** @var list<class-string> $providers */
    $providers = require base_path('bootstrap/providers.php');

    return array_values(array_filter(
        $providers,
        static fn (string $provider): bool => preg_match(
            '#^App\\\\Modules\\\\[A-Za-z]+\\\\Infrastructure\\\\Providers\\\\[A-Za-z]+ServiceProvider$#',
            $provider
        ) === 1
    ));
}
