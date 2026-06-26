<?php

declare(strict_types=1);

// These rules are verified by ArchTestCanaryTest to prevent vacuous passes.

foreach (architectureModuleNames() as $module) {
    foreach (architectureForeignModuleNamespaces($module) as $foreignModule) {
        arch("{$module} domain is isolated from {$foreignModule}")
            ->expect("App\\Modules\\{$module}\\Domain")
            ->not->toUse("{$foreignModule}\\*");

        arch("{$module} infrastructure is isolated from {$foreignModule}")
            ->expect("App\\Modules\\{$module}\\Infrastructure")
            ->not->toUse("{$foreignModule}\\*");

        arch("{$module} presentation is isolated from {$foreignModule}")
            ->expect("App\\Modules\\{$module}\\Presentation")
            ->not->toUse("{$foreignModule}\\*");

        arch("{$module} application does not access {$foreignModule} domain")
            ->expect("App\\Modules\\{$module}\\Application")
            ->not->toUse("{$foreignModule}\\Domain\\*");

        arch("{$module} application does not access {$foreignModule} infrastructure")
            ->expect("App\\Modules\\{$module}\\Application")
            ->not->toUse("{$foreignModule}\\Infrastructure\\*");

        arch("{$module} application does not access {$foreignModule} presentation")
            ->expect("App\\Modules\\{$module}\\Application")
            ->not->toUse("{$foreignModule}\\Presentation\\*");
    }
}

test('support namespace is available for module consumption', function (): void {
    expect(is_dir(app_path('Support')))->toBeTrue();

    foreach (architectureSharedNamespaces() as $namespace) {
        expect($namespace)->toStartWith('App\\');
    }
});
