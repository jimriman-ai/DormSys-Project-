<?php

declare(strict_types=1);

/**
 * G-REQ-03 — Stage-1 identity bind contract (maps T2-2 / T2-3 / B2a).
 *
 * Callers of approveRequestStageForTest (or create→submit→approve helpers) must
 * bind Stage-1 via fixture / equivalent, or live under Pest scopes that already bind.
 */

/**
 * @return list<string>
 */
function gReq03ExcludedPathPrefixes(): array
{
    return [
        'tests/Feature/Modules/Lottery/',
        'tests/Feature/Modules/Reporting/',
    ];
}

/**
 * Pest.php beforeEach binds Stage-1 for these Feature scopes (see tests/Pest.php).
 *
 * @return list<string>
 */
function gReq03PestStage1BoundPrefixes(): array
{
    return [
        'tests/Feature/Modules/Request/',
        'tests/Feature/Modules/Allocation/',
        'tests/Feature/Modules/CheckIn/',
        'tests/Feature/Mutation/',
    ];
}

/**
 * @return list<string>
 */
function gReq03Stage1BindMarkers(): array
{
    return [
        'bindStage1ApproverIdentityFixtureForTests',
        'bindStage1ConsoleApproverAsSnapshotSource',
        'createSubmittedStage1PersonalRequest',
        'Stage1ApproverIdentityReadContract',
    ];
}

function gReq03RelativePath(string $absolutePath): string
{
    $base = str_replace('\\', '/', base_path()).'/';
    $normalized = str_replace('\\', '/', $absolutePath);

    if (str_starts_with($normalized, $base)) {
        return substr($normalized, strlen($base));
    }

    return $normalized;
}

function gReq03IsExcluded(string $relative): bool
{
    foreach (gReq03ExcludedPathPrefixes() as $prefix) {
        if (str_starts_with($relative, $prefix)) {
            return true;
        }
    }

    // Helper definition / fixture libraries — not call-site contracts.
    if (str_ends_with($relative, '/support/mutation-principal.php')) {
        return true;
    }
    if (str_ends_with($relative, '/support/stage1-snapshot.php')) {
        return true;
    }
    if (str_ends_with($relative, '/support/stage1-console.php')) {
        return true;
    }

    return false;
}

function gReq03HasDbt3Marker(string $contents): bool
{
    return str_contains($contents, '@allowed-api-guard:');
}

function gReq03HasStage1Bind(string $contents, string $relative): bool
{
    foreach (gReq03Stage1BindMarkers() as $marker) {
        if (str_contains($contents, $marker)) {
            return true;
        }
    }

    foreach (gReq03PestStage1BoundPrefixes() as $prefix) {
        if (str_starts_with($relative, $prefix)) {
            return true;
        }
    }

    return false;
}

/**
 * Call sites of approveRequestStageForTest(...), excluding the function definition.
 *
 * @return list<int> 1-based line numbers
 */
function gReq03ApproveCallLines(string $contents): array
{
    $lines = preg_split("/\r\n|\n|\r/", $contents) ?: [];
    $hits = [];

    foreach ($lines as $index => $line) {
        if (preg_match('/^\s*function\s+approveRequestStageForTest\b/', $line) === 1) {
            continue;
        }
        if (preg_match('/\bapproveRequestStageForTest\s*\(/', $line) === 1) {
            $hits[] = $index + 1;
        }
    }

    return $hits;
}

function gReq03HasCreateSubmitApproveSequence(string $contents): bool
{
    $hasCreate = str_contains($contents, 'CreatePersonalRequestAction')
        || str_contains($contents, 'createDraftPersonalRequest');

    $hasSubmit = str_contains($contents, 'SubmitRequestAction');

    $hasApproveCall = gReq03ApproveCallLines($contents) !== [];

    return $hasCreate && $hasSubmit && $hasApproveCall;
}

/**
 * @return list<string> absolute paths
 */
function gReq03ScanPhpFiles(): array
{
    $root = base_path('tests');
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

it('requires Stage-1 bind for files that call approveRequestStageForTest', function (): void {
    $failures = [];

    foreach (gReq03ScanPhpFiles() as $absolute) {
        $relative = gReq03RelativePath($absolute);
        if (gReq03IsExcluded($relative)) {
            continue;
        }

        $contents = (string) file_get_contents($absolute);
        if (gReq03HasDbt3Marker($contents)) {
            continue;
        }

        $callLines = gReq03ApproveCallLines($contents);
        if ($callLines === []) {
            continue;
        }

        if (gReq03HasStage1Bind($contents, $relative)) {
            continue;
        }

        $failures[] = sprintf(
            'Stage-1 bind missing in: %s (approveRequestStageForTest at lines %s)',
            $relative,
            implode(', ', $callLines),
        );
    }

    expect($failures)->toBe([], implode("\n", $failures));
});

it('requires Stage-1 bind for create→submit→approve sequences', function (): void {
    $failures = [];

    foreach (gReq03ScanPhpFiles() as $absolute) {
        $relative = gReq03RelativePath($absolute);
        if (gReq03IsExcluded($relative)) {
            continue;
        }

        $contents = (string) file_get_contents($absolute);
        if (gReq03HasDbt3Marker($contents)) {
            continue;
        }

        if (! gReq03HasCreateSubmitApproveSequence($contents)) {
            continue;
        }

        if (gReq03HasStage1Bind($contents, $relative)) {
            continue;
        }

        $failures[] = "Stage-1 bind missing in: {$relative} (create→submit→approve sequence)";
    }

    expect($failures)->toBe([], implode("\n", $failures));
});
