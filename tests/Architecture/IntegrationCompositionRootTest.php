<?php

declare(strict_types=1);

use App\Providers\IntegrationServiceProvider;
use Illuminate\Support\ServiceProvider;
use Tests\Architecture\Support\ArchitectureGuard;

test('integration ports bind only in IntegrationServiceProvider', function (): void {
    $basePath = base_path();
    $violations = ArchitectureGuard::findPortBindingsInModuleProviders(
        $basePath,
        architectureIntegrationPortClasses()
    );

    $messages = array_map(
        static fn (array $violation): string => "{$violation['port']} bound in {$violation['path']}:{$violation['line']}",
        $violations
    );

    expect($violations)->toBe(
        [],
        "Cross-module integration ports must bind only in app/Providers/IntegrationServiceProvider.php:\n"
        .implode("\n", $messages)
    );
});

test('legacy module provider port bindings remain in their approved locations only', function (): void {
    $basePath = base_path();
    $legacyBindings = architectureLegacyModuleProviderPortBindings();

    foreach ($legacyBindings as $portClass => $approvedProviderPath) {
        $matches = ArchitectureGuard::findPortBindingsInModuleProviders($basePath, [$portClass]);
        $unexpected = array_values(array_filter(
            $matches,
            static fn (array $match): bool => $match['path'] !== str_replace('\\', '/', $approvedProviderPath)
        ));

        expect($unexpected)->toBe(
            [],
            "Legacy port {$portClass} may bind only in {$approvedProviderPath}"
        );
    }
});

test('integration service provider registers all approved integration ports', function (): void {
    $providerPath = base_path('app/Providers/IntegrationServiceProvider.php');
    $contents = file_get_contents($providerPath);

    expect($contents)->not->toBeFalse();

    foreach (architectureIntegrationPortClasses() as $portClass) {
        expect($contents)->toContain(class_basename($portClass));
    }
});

test('integration service provider remains the last bootstrap provider', function (): void {
    /** @var list<class-string> $providers */
    $providers = require base_path('bootstrap/providers.php');
    $lastKey = array_key_last($providers);
    if ($lastKey === null) {
        throw new UnexpectedValueException('Expected at least one bootstrap provider.');
    }

    expect($providers[$lastKey])->toBe(IntegrationServiceProvider::class);
});

test('integration service provider registers bindings in register not boot', function (): void {
    $reflection = new ReflectionClass(IntegrationServiceProvider::class);

    expect($reflection->hasMethod('register'))->toBeTrue();

    if ($reflection->hasMethod('boot')) {
        expect($reflection->getMethod('boot')->getDeclaringClass()->getName())->toBe(
            ServiceProvider::class,
            'IntegrationServiceProvider must not override boot() for port bindings; use register() only.'
        );
    }

    $fileName = $reflection->getFileName();
    if ($fileName === false) {
        throw new UnexpectedValueException('Expected integration service provider file name.');
    }

    $registerSource = file_get_contents($fileName);

    expect($registerSource)->not->toBeFalse();
    expect($registerSource)->toContain('singleton(');
});
