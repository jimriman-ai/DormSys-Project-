<?php

declare(strict_types=1);

/**
 * G-REQ-07 — Presentation must not use DB facade / raw query access.
 *
 * Complements G-REQ-04 (no Eloquent Model). Data access belongs in Infrastructure/Repositories.
 */

/**
 * @return list<string>
 */
function gReq07PresentationScanRoots(): array
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

function gReq07RelativePath(string $absolutePath): string
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
function gReq07ForbiddenTokens(): array
{
    return [
        'DB::',
        '\\DB::',
        '\\Illuminate\\Support\\Facades\\DB',
        'Illuminate\\Support\\Facades\\DB',
        'use Illuminate\\Support\\Facades\\DB',
    ];
}

/**
 * @param  list<string>  $roots
 * @return list<string>
 */
function gReq07CollectPhpFiles(array $roots): array
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
function gReq07FindRawQueryUsages(string $contents): array
{
    $hits = [];
    $lines = preg_split("/\r\n|\n|\r/", $contents) ?: [];

    foreach ($lines as $index => $line) {
        foreach (gReq07ForbiddenTokens() as $token) {
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

it('testPresentationDoesNotUseRawQueries', function (): void {
    $roots = gReq07PresentationScanRoots();
    $failures = [];

    foreach (gReq07CollectPhpFiles($roots) as $absolute) {
        $relative = gReq07RelativePath($absolute);
        $contents = (string) file_get_contents($absolute);

        if (str_contains($contents, '@allowed-raw-query')) {
            continue;
        }

        foreach (gReq07FindRawQueryUsages($contents) as $hit) {
            $failures[] = sprintf(
                '%s:%d [%s] %s',
                $relative,
                $hit['line'],
                $hit['token'],
                $hit['snippet'],
            );
        }
    }

    expect($failures)->toBe([], "Presentation raw DB query violations:\n".implode("\n", $failures));
});
