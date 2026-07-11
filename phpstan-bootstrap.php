<?php

declare(strict_types=1);

/**
 * PHPStan / Larastan analysis bootstrap.
 *
 * Why this exists:
 * - spatie/ray helpers.php registers a shutdown function that calls ray().
 * - spatie/laravel-ray RayServiceProvider::boot() → setProjectName() calls
 *   ray()->project(...) whenever APP_NAME !== 'Laravel' (DormSys).
 * - Larastan boots the Laravel app in analysis workers; ray() resolves
 *   Spatie\LaravelRay\Ray through the container.
 * - When workers are already near their memory ceiling, that container work
 *   (and Whoops/Ray shutdown handling) turns recoverable pressure into a
 *   fatal incomplete parallel analysis.
 *
 * This bootstrap disables Ray for analysis and pre-sets Ray::$projectName so
 * setProjectName() skips the boot-time ray() call. It does not remove the
 * package or change production application behavior.
 *
 * Note: PHPStan worker processes get their memory ceiling from CLI
 * `--memory-limit` (composer script / CI use 1G). ini_set here helps the
 * parent process only; prefer passing `--memory-limit=1G` for full runs.
 */
ini_set('memory_limit', '1G');

putenv('RAY_ENABLED=false');
$_ENV['RAY_ENABLED'] = 'false';
$_SERVER['RAY_ENABLED'] = 'false';

putenv('SEND_EXCEPTIONS_TO_RAY=false');
$_ENV['SEND_EXCEPTIONS_TO_RAY'] = 'false';
$_SERVER['SEND_EXCEPTIONS_TO_RAY'] = 'false';

if (class_exists(Spatie\Ray\Ray::class)) {
    Spatie\Ray\Ray::$projectName = 'phpstan';
}
