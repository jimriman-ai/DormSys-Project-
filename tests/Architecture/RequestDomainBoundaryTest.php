<?php

declare(strict_types=1);

/**
 * G-REQ-04 — Request presentation/HTTP boundary: no direct Eloquent in UI/HTTP layers.
 *
 * DormSys layout uses Presentation/Http + Presentation/Livewire (not app/Modules/Request/Http/).
 * Vacuous PASS if neither scan root exists.
 */

/**
 * @return list<string> Absolute directories to scan (must exist).
 */
function gReq04ScanRoots(): array
{
    $candidates = [
        base_path('app/Modules/Request/Http'),
        base_path('app/Modules/Request/Presentation/Http'),
        base_path('app/Modules/Request/Presentation/Livewire'),
    ];

    return array_values(array_filter(
        $candidates,
        static fn (string $dir): bool => is_dir($dir),
    ));
}

function gReq04RelativePath(string $absolutePath): string
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
function gReq04EloquentLeakPatterns(): array
{
    // Note: bare "::find" omitted — it false-positives on findOrFail / findMany.
    return [
        '::where(',
        '::find(',
        '::create(',
        '::query(',
        '->save()',
        '->delete()',
        '->update(',
        'use App\\Models\\',
    ];
}

/**
 * @return list<string> absolute PHP paths
 */
function gReq04CollectPhpFiles(array $roots): array
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
 * @return list<array{pattern: string, line: int, snippet: string}>
 */
function gReq04FindEloquentLeaks(string $contents): array
{
    $hits = [];
    $lines = preg_split("/\r\n|\n|\r/", $contents) ?: [];

    foreach ($lines as $index => $line) {
        foreach (gReq04EloquentLeakPatterns() as $pattern) {
            if (str_contains($line, $pattern)) {
                $hits[] = [
                    'pattern' => $pattern,
                    'line' => $index + 1,
                    'snippet' => trim($line),
                ];
                break;
            }
        }
    }

    return $hits;
}

it('testNoDirectEloquentInHttpLayer', function (): void {
    $roots = gReq04ScanRoots();

    if ($roots === []) {
        expect(true)->toBeTrue();

        return;
    }

    $failures = [];

    foreach (gReq04CollectPhpFiles($roots) as $absolute) {
        $relative = gReq04RelativePath($absolute);
        $contents = (string) file_get_contents($absolute);

        if (str_contains($contents, '@allowed-eloquent-direct')) {
            continue;
        }

        foreach (gReq04FindEloquentLeaks($contents) as $hit) {
            $failures[] = sprintf(
                'Eloquent leak in HTTP/presentation layer: %s:%d [pattern %s] %s',
                $relative,
                $hit['line'],
                $hit['pattern'],
                $hit['snippet'],
            );
        }
    }

    expect($failures)->toBe([], implode("\n", $failures));
});
