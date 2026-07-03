<?php

declare(strict_types=1);

use App\Modules\Reporting\Application\Contracts\ReportingReadContract;
use App\Modules\Reporting\Application\Services\ReportingReadService;

arch('reporting module does not import audit infrastructure')
    ->expect('App\Modules\Reporting')
    ->not->toUse('App\Modules\Audit\Infrastructure\*');

test('reporting read contract is bound to the reporting read service', function (): void {
    expect(app(ReportingReadContract::class))->toBeInstanceOf(ReportingReadService::class);
});

test('only audit history source adapter references audit history read contract', function (): void {
    $reportingPath = app_path('Modules/Reporting');
    $violations = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($reportingPath)) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();

        if (str_contains($path, 'AuditHistorySourceReadAdapter.php')) {
            continue;
        }

        $contents = file_get_contents($path);

        if ($contents !== false && str_contains($contents, 'AuditHistoryReadContract')) {
            $violations[] = $path;
        }
    }

    expect($violations)->toBe([]);
});
