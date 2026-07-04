<?php

declare(strict_types=1);

use Tests\Architecture\Support\ArchitectureGuard;

test('forbidden import static scan finds no obvious layer violations', function (): void {
    $basePath = base_path();
    $violations = ArchitectureGuard::scanForbiddenImports($basePath);

    $messages = array_map(
        static fn (array $violation): string => "{$violation['path']}:{$violation['line']} {$violation['message']}",
        $violations
    );

    expect($violations)->toBe([], implode("\n", $messages));
});

test('forbidden import static scan matches the standalone architecture scan script', function (): void {
    $scriptPath = base_path('scripts/architecture/forbidden-imports-scan.php');

    expect(is_file($scriptPath))->toBeTrue();

    $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg($scriptPath).' 2>&1';
    $output = [];
    $exitCode = 0;

    exec($command, $output, $exitCode);

    expect($exitCode)->toBe(0, implode("\n", $output));
});
