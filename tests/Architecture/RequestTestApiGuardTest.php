<?php

declare(strict_types=1);

/**
 * G-REQ-02 — Prevent new Request Feature tests from using api guard while DBT-3 is frozen.
 *
 * Maps T2 cluster B2b. Whitelisted pre-existing leaks require `@allowed-api-guard:` marker.
 */

/**
 * @return list<string> Paths relative to project root, forward slashes.
 */
function gReq02ApiGuardWhitelist(): array
{
    return [
        'tests/Feature/Modules/Request/support/http-mutation.php',
        'tests/Feature/Modules/Request/RequestListDetailNavigationUiFlowTest.php',
        'tests/Feature/Modules/Request/RequestShowUiFlowTest.php',
        'tests/Feature/Modules/Request/RequestUiFlowTest.php',
        'tests/Feature/Modules/Request/RequestListFilteringSortingPaginationUiFlowTest.php',
    ];
}

/**
 * @return list<string> Absolute paths to PHP files under Request Feature tests.
 */
function gReq02RequestFeaturePhpFiles(): array
{
    $root = base_path('tests/Feature/Modules/Request');
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    $files = [];
    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with(strtolower($file->getFilename()), '.php')) {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

function gReq02RelativePath(string $absolutePath): string
{
    $base = str_replace('\\', '/', base_path()).'/';
    $normalized = str_replace('\\', '/', $absolutePath);

    if (str_starts_with($normalized, $base)) {
        return substr($normalized, strlen($base));
    }

    return $normalized;
}

/**
 * @return list<array{line: int, snippet: string}>
 */
function gReq02FindApiGuardUsages(string $contents): array
{
    $patterns = [
        // actingAs($x, 'api') / Livewire::actingAs($x, "api") — any spacing
        '/\bactingAs\s*\([^;]*?,\s*[\'"]api[\'"]\s*\)/i',
        '/\bSanctum::actingAs\s*\(/i',
        '/\bPassport::actingAs\s*\(/i',
        '/->withToken\s*\(/i',
        '/Authorization:\s*Bearer/i',
    ];

    $hits = [];
    $lines = preg_split("/\r\n|\n|\r/", $contents) ?: [];

    foreach ($lines as $index => $line) {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line) === 1) {
                $hits[] = [
                    'line' => $index + 1,
                    'snippet' => trim($line),
                ];
                break;
            }
        }
    }

    return $hits;
}

it('rejects unauthorized api guard usage outside the DBT-3 whitelist', function (): void {
    $whitelist = gReq02ApiGuardWhitelist();
    $failures = [];

    foreach (gReq02RequestFeaturePhpFiles() as $absolutePath) {
        $relative = gReq02RelativePath($absolutePath);
        $contents = (string) file_get_contents($absolutePath);
        $hits = gReq02FindApiGuardUsages($contents);

        if ($hits === []) {
            continue;
        }

        if (in_array($relative, $whitelist, true)) {
            continue;
        }

        foreach ($hits as $hit) {
            $failures[] = sprintf(
                'Unauthorized api guard usage in: %s:%d (%s)',
                $relative,
                $hit['line'],
                $hit['snippet'],
            );
        }
    }

    expect($failures)->toBe([], implode("\n", $failures));
});

it('requires @allowed-api-guard marker on every whitelisted Request api-guard file', function (): void {
    $failures = [];

    foreach (gReq02ApiGuardWhitelist() as $relative) {
        $absolute = base_path($relative);
        expect(is_file($absolute))->toBeTrue("Whitelisted path missing on disk: {$relative}");

        $contents = (string) file_get_contents($absolute);
        $hits = gReq02FindApiGuardUsages($contents);

        if ($hits === []) {
            continue;
        }

        if (! str_contains($contents, '@allowed-api-guard:')) {
            $failures[] = "Whitelisted file missing marker: {$relative}";
        }
    }

    expect($failures)->toBe([], implode("\n", $failures));
});

it('documents only the approved DBT-3 whitelist paths', function (): void {
    foreach (gReq02ApiGuardWhitelist() as $relative) {
        expect($relative)->toStartWith('tests/Feature/Modules/Request/');
        expect(is_file(base_path($relative)))->toBeTrue("Whitelist entry not found: {$relative}");
    }
});
