<?php

declare(strict_types=1);

test('mutation security baseline freeze artifact exists and remains static', function (): void {
    $path = app_path('Application/Mutation/MUTATION_SECURITY_BASELINE_FREEZE.md');

    expect(is_file($path))->toBeTrue();

    $contents = file_get_contents($path);
    expect($contents)->toBeString()
        ->and($contents)->toContain('Baseline Declaration')
        ->and($contents)->toContain('Threat Context')
        ->and($contents)->toContain('Freeze Rule')
        ->and($contents)->toContain('MPEP')
        ->and($contents)->toContain('fails closed')
        ->and($contents)->toContain('not a roadmap')
        ->and($contents)->toContain('confirmed defect')
        ->and($contents)->not->toContain('## Roadmap')
        ->and($contents)->not->toContain('Future Work');
});
