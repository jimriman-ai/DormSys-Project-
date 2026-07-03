<?php

declare(strict_types=1);

use App\Modules\Reporting\Application\Services\ReportingProvenanceFactory;

it('builds T0 provenance with null refresh metadata', function (): void {
    $factory = new ReportingProvenanceFactory;

    $provenance = $factory->forT0([
        'entityType' => 'request',
        'entityId' => '01932f4a-7b2c-7000-8000-000000000001',
        'page' => 1,
        'perPage' => 50,
    ], includeArchived: false);

    expect($provenance->sourceTier)->toBe('T0');
    expect($provenance->refreshedAt)->toBeNull();
    expect($provenance->projectionVersion)->toBeNull();
    expect($provenance->includeArchived)->toBeFalse();
    expect($provenance->filterHash)->not->toBe('');
});

it('changes filter hash when include archived flag changes', function (): void {
    $factory = new ReportingProvenanceFactory;
    $filters = [
        'entityType' => 'request',
        'entityId' => '01932f4a-7b2c-7000-8000-000000000001',
        'page' => 1,
        'perPage' => 50,
    ];

    $withoutArchived = $factory->forT0($filters, includeArchived: false);
    $withArchived = $factory->forT0($filters, includeArchived: true);

    expect($withoutArchived->filterHash)->not->toBe($withArchived->filterHash);
});
