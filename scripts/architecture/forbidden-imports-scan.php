#!/usr/bin/env php
<?php

declare(strict_types=1);

$repoRoot = dirname(__DIR__, 2);

require $repoRoot.'/vendor/autoload.php';

$app = require $repoRoot.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

require $repoRoot.'/tests/Architecture/architecture.php';
require $repoRoot.'/tests/Architecture/Support/ArchitectureGuard.php';

use Illuminate\Contracts\Console\Kernel;
use Tests\Architecture\Support\ArchitectureGuard;

$violations = ArchitectureGuard::scanForbiddenImports($repoRoot);

$matrixForeignDomainViolations = ArchitectureGuard::findMatrixApplicationForeignDomainImports(
    $repoRoot,
    architectureModuleNames()
);

$checkInAllowlist = architectureCheckInForeignDomainImportAllowlist();
$checkInForeignDomainViolations = array_values(array_filter(
    ArchitectureGuard::findForeignDomainImports($repoRoot, 'CheckIn'),
    static function (array $finding) use ($checkInAllowlist): bool {
        $importBase = str_contains($finding['import'], ' as ')
            ? trim(explode(' as ', $finding['import'])[0])
            : $finding['import'];

        $allowed = $checkInAllowlist[$finding['path']] ?? [];

        return ! in_array($importBase, $allowed, true);
    }
));

$integrationPortViolations = ArchitectureGuard::findPortBindingsInModuleProviders(
    $repoRoot,
    architectureIntegrationPortClasses()
);

$legacyAdapterViolations = [];
$legacyPaths = architectureLegacyCrossModuleAdapterPaths();

foreach (architectureDiscoverActiveModuleNames() as $module) {
    foreach (['Application/Adapters', 'Infrastructure/Adapters'] as $layer) {
        $findings = ArchitectureGuard::findCrossModuleApplicationImports(
            $repoRoot,
            "app/Modules/{$module}/{$layer}",
            $module
        );

        foreach ($findings as $finding) {
            if (! in_array($finding['path'], $legacyPaths, true)) {
                $legacyAdapterViolations[] = $finding;
            }
        }
    }
}

$allViolations = [
    'forbidden_imports' => $violations,
    'matrix_foreign_domain' => $matrixForeignDomainViolations,
    'checkin_foreign_domain' => $checkInForeignDomainViolations,
    'integration_port_bindings' => $integrationPortViolations,
    'unregistered_cross_module_adapters' => $legacyAdapterViolations,
];

$hasFailure = false;

foreach ($allViolations as $category => $items) {
    if ($items === []) {
        continue;
    }

    $hasFailure = true;
    fwrite(STDOUT, strtoupper(str_replace('_', ' ', $category)).PHP_EOL);

    foreach ($items as $item) {
        if (isset($item['port'])) {
            fwrite(STDOUT, sprintf(
                "  - %s:%d %s bound in module provider\n",
                $item['path'],
                $item['line'],
                $item['port']
            ));

            continue;
        }

        if (isset($item['pattern'])) {
            fwrite(STDOUT, sprintf(
                "  - %s:%d %s\n",
                $item['path'],
                $item['line'],
                $item['message']
            ));

            continue;
        }

        if (isset($item['import'])) {
            fwrite(STDOUT, sprintf(
                "  - %s:%d %s\n",
                $item['path'],
                $item['line'],
                $item['import']
            ));

            continue;
        }
    }

    fwrite(STDOUT, PHP_EOL);
}

if ($hasFailure) {
    fwrite(STDERR, "Architecture forbidden-import scan failed.\n");

    exit(1);
}

fwrite(STDOUT, "Architecture forbidden-import scan passed.\n");

exit(0);
