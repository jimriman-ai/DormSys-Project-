<?php

declare(strict_types=1);

use Tests\Architecture\Support\ArchitectureGuard;

/**
 * Non-blocking visibility checks for tolerated legacy debt and coverage gaps.
 * Run locally with: composer run arch:advisory
 */
test('legacy cross-module adapter registry matches files on disk', function (): void {
    $missing = [];

    foreach (architectureLegacyCrossModuleAdapterPaths() as $relativePath) {
        if (! is_file(base_path($relativePath))) {
            $missing[] = $relativePath;
        }
    }

    expect($missing)->toBe([], 'Stale legacy adapter registry entries: '.implode(', ', $missing));
})->group('architecture-advisory');

test('check-in foreign domain debt remains a single allowlisted import', function (): void {
    $findings = ArchitectureGuard::findForeignDomainImports(base_path(), 'CheckIn');

    expect($findings)->toHaveCount(1);
    expect($findings[0]['path'])->toBe('app/Modules/CheckIn/Application/Services/OperatorRoleGate.php');
    expect($findings[0]['import'])->toBe('App\\Modules\\Identity\\Domain\\ValueObjects\\UserId');
})->group('architecture-advisory');

test('reporting infrastructure foreign application imports remain within legacy adapters only', function (): void {
    $basePath = base_path();
    $legacyPaths = architectureLegacyCrossModuleAdapterPaths();
    $findings = ArchitectureGuard::findCrossModuleApplicationImports(
        $basePath,
        'app/Modules/Reporting/Infrastructure/Adapters',
        'Reporting'
    );

    $unexpected = array_values(array_filter(
        $findings,
        static fn (array $finding): bool => ! in_array($finding['path'], $legacyPaths, true)
    ));

    expect($unexpected)->toBe([]);
})->group('architecture-advisory');
