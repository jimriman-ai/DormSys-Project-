<?php

declare(strict_types=1);

/**
 * G-REQ-06 — Presentation must not dispatch Commands/Jobs directly.
 *
 * Bus/Job dispatch belongs in Application/Services only.
 * Scans all modules' Presentation/Http + Presentation/Livewire.
 */

/**
 * @return list<string>
 */
function gReq06PresentationScanRoots(): array
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

function gReq06RelativePath(string $absolutePath): string
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
function gReq06ForbiddenTokens(): array
{
    return [
        'Bus::dispatch(',
        'dispatch(',
        '\\Illuminate\\Support\\Facades\\Bus',
        'Illuminate\\Support\\Facades\\Bus',
        '->dispatch(',
    ];
}

/**
 * @param  list<string>  $roots
 * @return list<string>
 */
function gReq06CollectPhpFiles(array $roots): array
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
function gReq06FindCommandDispatchUsages(string $contents): array
{
    $hits = [];
    $lines = preg_split("/\r\n|\n|\r/", $contents) ?: [];

    foreach ($lines as $index => $line) {
        foreach (gReq06ForbiddenTokens() as $token) {
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

it('testPresentationDoesNotDispatchCommands', function (): void {
    $roots = gReq06PresentationScanRoots();
    $failures = [];

    foreach (gReq06CollectPhpFiles($roots) as $absolute) {
        $relative = gReq06RelativePath($absolute);
        $contents = (string) file_get_contents($absolute);

        if (str_contains($contents, '@allowed-command-dispatch')) {
            continue;
        }

        foreach (gReq06FindCommandDispatchUsages($contents) as $hit) {
            $failures[] = sprintf(
                '%s:%d [%s] %s',
                $relative,
                $hit['line'],
                $hit['token'],
                $hit['snippet'],
            );
        }
    }

    expect($failures)->toBe([], "Presentation command/job dispatch violations:\n".implode("\n", $failures));
});
