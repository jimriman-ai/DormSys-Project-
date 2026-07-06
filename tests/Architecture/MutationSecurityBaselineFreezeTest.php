<?php

declare(strict_types=1);

test('mutation security hard freeze artifact is present and non-expanding', function (): void {
    $path = app_path('Application/Mutation/MUTATION_SECURITY_BASELINE_FREEZE.md');

    expect(is_file($path))->toBeTrue();

    $contents = file_get_contents($path);
    expect($contents)->toBeString();

    expect(strlen((string) $contents))->toBeLessThan(1500)
        ->and(substr_count((string) $contents, "\n## "))->toBe(0)
        ->and($contents)->toContain('fails closed')
        ->and($contents)->toContain('No further mutation-security evolution is allowed')
        ->and($contents)->toContain('not a roadmap');
});
