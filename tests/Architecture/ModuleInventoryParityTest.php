<?php

declare(strict_types=1);

use Tests\Architecture\Support\ArchitectureGuard;

test('every active module is in the full matrix or the documented exclusion list', function (): void {
    $activeModules = architectureDiscoverActiveModuleNames();
    $matrixModules = architectureModuleNames();
    $excludedModules = architectureMatrixExcludedActiveModules();

    expect($activeModules)->not->toBeEmpty();

    $uncovered = array_values(array_diff($activeModules, $matrixModules, $excludedModules));

    expect($uncovered)->toBe(
        [],
        'Active modules must be listed in architectureModuleNames() or architectureMatrixExcludedActiveModules(): '
        .implode(', ', $uncovered)
    );
});

test('every matrix module is active on disk and registered in bootstrap', function (): void {
    $activeModules = architectureDiscoverActiveModuleNames();
    $bootstrapProviders = architectureBootstrapModuleServiceProviders();

    foreach (architectureModuleNames() as $module) {
        expect($activeModules)->toContain($module);

        $expectedProvider = "App\\Modules\\{$module}\\Infrastructure\\Providers\\{$module}ServiceProvider";
        expect($bootstrapProviders)->toContain($expectedProvider);
    }
});

test('matrix-excluded active modules remain registered in bootstrap', function (): void {
    $bootstrapProviders = architectureBootstrapModuleServiceProviders();

    foreach (architectureMatrixExcludedActiveModules() as $module) {
        $expectedProvider = "App\\Modules\\{$module}\\Infrastructure\\Providers\\{$module}ServiceProvider";

        expect(architectureDiscoverActiveModuleNames())->toContain($module);
        expect($bootstrapProviders)->toContain($expectedProvider);
    }
});

test('matrix-excluded modules do not silently join the full matrix without an explicit inventory change', function (): void {
    foreach (architectureMatrixExcludedActiveModules() as $module) {
        expect(architectureModuleNames())->not->toContain($module);
    }
});

test('bootstrap module providers and discovered active modules stay in parity', function (): void {
    $activeModules = architectureDiscoverActiveModuleNames();

    $bootstrapModules = array_map(
        static fn (string $provider): string => (string) preg_replace(
            '#^App\\\\Modules\\\\([A-Za-z]+)\\\\Infrastructure\\\\Providers\\\\.+$#',
            '$1',
            $provider
        ),
        architectureBootstrapModuleServiceProviders()
    );

    sort($bootstrapModules);

    expect($bootstrapModules)->toEqual($activeModules);
});

test('matrix modules application layer has no foreign domain imports', function (): void {
    $basePath = base_path();
    $findings = ArchitectureGuard::findMatrixApplicationForeignDomainImports($basePath, architectureModuleNames());

    $messages = array_map(
        static fn (array $finding): string => "{$finding['module']} {$finding['path']}:{$finding['line']} {$finding['import']}",
        $findings
    );

    expect($findings)->toBe([], implode("\n", $messages));
});
