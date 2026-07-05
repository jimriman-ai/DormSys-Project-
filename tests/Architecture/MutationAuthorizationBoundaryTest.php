<?php

declare(strict_types=1);

use App\Application\Mutation\Registry\ExemptMutationActionRegistry;
use App\Application\Mutation\Registry\PendingMutationAuthorizationRegistry;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;

/**
 * @return array<string, string> class-name => absolute file path
 */
function discoverMutationActionClasses(): array
{
    $discovered = [];

    $patterns = [
        app_path('Modules/*/Application/Services/*Action.php'),
        app_path('Application/Auth/*Action.php'),
    ];

    foreach ($patterns as $pattern) {
        foreach (glob($pattern) ?: [] as $path) {
            $className = mutationActionClassFromPath($path);
            $discovered[$className] = $path;
        }
    }

    ksort($discovered);

    return $discovered;
}

function mutationActionClassFromPath(string $path): string
{
    $normalized = str_replace('\\', '/', $path);
    $appPath = str_replace('\\', '/', app_path());
    $relative = substr($normalized, strlen($appPath) + 1);
    $relative = substr($relative, 0, -4);

    return 'App\\'.str_replace('/', '\\', $relative);
}

test('all mutation action classes are exempt, pending, or enforced by MPEP', function (): void {
    $unaccounted = [];

    foreach (discoverMutationActionClasses() as $className => $path) {
        if (ExemptMutationActionRegistry::isExempt($className)) {
            continue;
        }

        if (PendingMutationAuthorizationRegistry::isPending($className)) {
            continue;
        }

        $contents = file_get_contents($path);

        if ($contents !== false && str_contains($contents, MutationPolicyEnforcementPoint::class)) {
            continue;
        }

        $unaccounted[$className] = $path;
    }

    expect($unaccounted)->toBe([]);
});

test('business mutation actions invoke MPEP or are explicitly registered', function (): void {
    $violations = [];

    foreach (discoverMutationActionClasses() as $className => $path) {
        if (ExemptMutationActionRegistry::isExempt($className)) {
            continue;
        }

        if (PendingMutationAuthorizationRegistry::isPending($className)) {
            continue;
        }

        $contents = file_get_contents($path);

        if ($contents === false || ! str_contains($contents, MutationPolicyEnforcementPoint::class)) {
            $violations[] = $className;
        }
    }

    expect($violations)->toBe([]);
});

test('exempt and pending mutation action registries do not overlap', function (): void {
    $overlap = array_values(array_intersect(
        ExemptMutationActionRegistry::all(),
        PendingMutationAuthorizationRegistry::all(),
    ));

    expect($overlap)->toBe([]);
});
