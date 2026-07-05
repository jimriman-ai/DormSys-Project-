<?php

declare(strict_types=1);

test('each module has a service provider class', function (): void {
    foreach (architectureModuleServiceProviders() as $providerClass) {
        expect(class_exists($providerClass))
            ->toBeTrue("Expected service provider [{$providerClass}] to exist.");
    }
});

test('each module service provider is registered in bootstrap', function (): void {
    /** @var list<class-string> $registeredProviders */
    $registeredProviders = require dirname(__DIR__, 2).'/bootstrap/providers.php';

    foreach (architectureModuleServiceProviders() as $providerClass) {
        expect($registeredProviders)->toContain($providerClass);
    }
});

test('each module service provider is bootable', function (): void {
    $providerClasses = architectureModuleServiceProviders();

    expect($providerClasses)->not->toBeEmpty();

    foreach ($providerClasses as $providerClass) {
        $provider = app()->resolveProvider($providerClass);

        if (method_exists($provider, 'boot')) {
            $provider->boot();
        }
    }
});

test('each module exposes the required layer directories', function (): void {
    $layers = ['Domain', 'Application', 'Infrastructure', 'Presentation'];

    foreach (architectureModuleNames() as $module) {
        foreach ($layers as $layer) {
            $path = app_path("Modules/{$module}/{$layer}");

            expect(is_dir($path))
                ->toBeTrue("Expected directory [{$path}] to exist for module [{$module}].");
        }
    }
});
