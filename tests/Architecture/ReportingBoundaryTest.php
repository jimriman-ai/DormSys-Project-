<?php

declare(strict_types=1);

use App\Modules\Reporting\Application\Contracts\ReportingReadContract;

arch('reporting module does not import audit infrastructure')
    ->expect('App\Modules\Reporting')
    ->not->toUse('App\Modules\Audit\Infrastructure\*');

test('reporting read contract is bound to the reporting read service', function (): void {
    app(ReportingReadContract::class);
});

test('reporting module does not reference audit history read contract directly', function (): void {
    $reportingPath = app_path('Modules/Reporting');
    $violations = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($reportingPath)) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        $contents = file_get_contents($path);

        if ($contents !== false && str_contains($contents, 'AuditHistoryReadContract')) {
            $violations[] = $path;
        }
    }

    expect($violations)->toBe([]);
});

test('reporting inner read actions do not reference audit authorization port directly', function (): void {
    $allowed = [
        'ReportingReadService.php',
        'ProjectionRefreshInputService.php',
    ];
    $servicesPath = app_path('Modules/Reporting/Application/Services');
    $violations = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($servicesPath)) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $basename = $file->getBasename();

        if (in_array($basename, $allowed, true)) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        if ($contents !== false && str_contains($contents, 'AuditAuthorizationPort')) {
            $violations[] = $file->getPathname();
        }
    }

    expect($violations)->toBe([]);
});
