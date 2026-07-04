<?php

declare(strict_types=1);

$modules = array_map(
    static fn (string $dir): string => basename($dir),
    glob('app/Modules/*', GLOB_ONLYDIR) ?: []
);

$issues = [];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Modules'));

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());

    if (! preg_match('#app/Modules/([^/]+)/([^/]+)/#', $path, $match)) {
        continue;
    }

    $module = $match[1];
    $layer = $match[2];
    $content = file_get_contents($path);

    if (! preg_match_all('/^use App\\\\Modules\\\\([^\\\\]+)\\\\([^\\\\]+)\\\\/m', $content, $uses, PREG_SET_ORDER)) {
        continue;
    }

    foreach ($uses as $use) {
        $foreignModule = $use[1];
        $foreignLayer = $use[2];

        if ($foreignModule === $module) {
            continue;
        }

        if (! in_array($foreignLayer, ['Domain', 'Infrastructure', 'Presentation'], true)) {
            continue;
        }

        if ($layer === 'Domain') {
            $issues[] = ['Domain leak', $path, $use[0], $foreignModule, $foreignLayer];
        }

        if ($layer === 'Application' && $foreignLayer === 'Domain') {
            $issues[] = ['App->foreign Domain', $path, $use[0], $foreignModule, $foreignLayer];
        }

        if ($layer === 'Infrastructure') {
            $issues[] = ['Infra cross-module', $path, $use[0], $foreignModule, $foreignLayer];
        }
    }
}

foreach ($issues as $issue) {
    echo implode(' | ', $issue).PHP_EOL;
}

echo 'TOTAL: '.count($issues).PHP_EOL;
