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
