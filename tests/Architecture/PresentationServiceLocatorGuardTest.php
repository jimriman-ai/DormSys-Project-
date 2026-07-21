<?php

declare(strict_types=1);

/**
 * G-REQ-08 — Presentation must not use explicit App::make / App::makeWith service locator.
 *
 * Option 3: app() in Livewire is an accepted pattern and is intentionally not scanned.
 */

/**
 * @return list<string>
 */
function gReq08PresentationScanRoots(): array
{
    $modulesRoot = base_path('app/Modules');
    if (! is_dir($modulesRoot)) {
        return [];
    }

    $roots = [];
    foreach (scandir($modulesRoot) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $modulePath = $modulesRoot.DIRECTORY_SEPARATOR.$entry;
        if (! is_dir($modulePath)) {
            continue;
        }

        foreach (['Http', 'Livewire'] as $layer) {
            $candidate = $modulePath.DIRECTORY_SEPARATOR.'Presentation'.DIRECTORY_SEPARATOR.$layer;
            if (is_dir($candidate)) {
                $roots[] = $candidate;
            }
        }
    }

    sort($roots);

    return $roots;
}

function gReq08RelativePath(string $absolutePath): string
{
    $base = str_replace('\\', '/', base_path()).'/';
    $normalized = str_replace('\\', '/', $absolutePath);

    if (str_starts_with($normalized, $base)) {
        return substr($normalized, strlen($base));
    }

    return $normalized;
}

/**
 * @return list<string>
 */
function gReq08ForbiddenTokens(): array
{
    return [
        'App::make(',
        'App::makeWith(',
        '\\App::make(',
        '\\App::makeWith(',
    ];
}

/**
 * @return list<string>
 */
function gReq08CollectPhpFiles(array $roots): array
{
    $files = [];

    foreach ($roots as $root) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with(strtolower($file->getFilename()), '.php')) {
                $files[] = $file->getPathname();
            }
        }
    }

    sort($files);

    return $files;
}

/**
 * @return list<array{token: string, line: int, snippet: string}>
 */
function gReq08FindServiceLocatorUsages(string $contents): array
{
    $hits = [];
    $lines = preg_split("/\r\n|\n|\r/", $contents) ?: [];

    foreach ($lines as $index => $line) {
        foreach (gReq08ForbiddenTokens() as $token) {
            if (str_contains($line, $token)) {
                $hits[] = [
                    'token' => $token,
                    'line' => $index + 1,
                    'snippet' => trim($line),
                ];
                break;
            }
        }
    }

    return $hits;
}

it('testPresentationDoesNotUseExplicitServiceLocator', function (): void {
    $roots = gReq08PresentationScanRoots();
    $failures = [];

    foreach (gReq08CollectPhpFiles($roots) as $absolute) {
        $relative = gReq08RelativePath($absolute);
        $contents = (string) file_get_contents($absolute);

        if (str_contains($contents, '@allowed-service-locator')) {
            continue;
        }

        foreach (gReq08FindServiceLocatorUsages($contents) as $hit) {
            $failures[] = sprintf(
                '%s:%d [%s] %s',
                $relative,
                $hit['line'],
                $hit['token'],
                $hit['snippet'],
            );
        }
    }

    expect($failures)->toBe([], "Presentation explicit service-locator violations:\n".implode("\n", $failures));
});
