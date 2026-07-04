<?php

declare(strict_types=1);

use Tests\Architecture\Support\ArchitectureGuard;

test('cross-module adapters outside integrations remain within the legacy registry', function (): void {
    $basePath = base_path();
    $legacyPaths = architectureLegacyCrossModuleAdapterPaths();
    $scanRoots = [
        ['path' => 'app/Modules', 'layers' => ['Application/Adapters', 'Infrastructure/Adapters']],
    ];

    $unregistered = [];

    foreach ($scanRoots as $root) {
        foreach (architectureDiscoverActiveModuleNames() as $module) {
            foreach ($root['layers'] as $layer) {
                $relativePath = "{$root['path']}/{$module}/{$layer}";
                $findings = ArchitectureGuard::findCrossModuleApplicationImports($basePath, $relativePath, $module);

                foreach ($findings as $finding) {
                    if (in_array($finding['path'], $legacyPaths, true)) {
                        continue;
                    }

                    $unregistered[] = "{$finding['path']}:{$finding['line']} imports {$finding['import']}";
                }
            }
        }
    }

    expect($unregistered)->toBe(
        [],
        "New cross-module adapters must live under app/Integrations/ and bind in IntegrationServiceProvider.\n"
        ."Legacy adapters must be listed in architectureLegacyCrossModuleAdapterPaths():\n"
        .implode("\n", $unregistered)
    );
});

test('integration bridges live under app integrations namespace', function (): void {
    $integrationsPath = base_path('app/Integrations');

    expect(is_dir($integrationsPath))->toBeTrue();

    $bridgeFiles = glob($integrationsPath.'/*/*.php') ?: [];

    expect($bridgeFiles)->not->toBeEmpty();

    foreach ($bridgeFiles as $bridgeFile) {
        $contents = file_get_contents($bridgeFile);

        expect($contents)->not->toBeFalse();
        expect($contents)->toContain('namespace App\\Integrations\\');
    }
});

test('new application adapters directory cannot grow without legacy registry update', function (): void {
    $adaptersPath = base_path('app/Modules/Lottery/Application/Adapters');
    $files = glob($adaptersPath.'/*.php') ?: [];
    $legacyPaths = architectureLegacyCrossModuleAdapterPaths();

    $unexpected = [];

    foreach ($files as $file) {
        $relativePath = 'app/Modules/Lottery/Application/Adapters/'.basename($file);

        if (! in_array($relativePath, $legacyPaths, true)) {
            $unexpected[] = $relativePath;
        }
    }

    expect($unexpected)->toBe(
        [],
        'Application/Adapters is closed for new cross-module adapters. Use app/Integrations/ instead: '
        .implode(', ', $unexpected)
    );
});
