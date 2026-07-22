<?php

declare(strict_types=1);

/**
 * Cross-process mutex for suites that share the Sail `testing` database.
 *
 * HD-02 Option B / G-REQ: parallel artisan test processes deadlocked on RefreshDatabase.
 * Holding an exclusive flock for the duration of each test serializes those processes.
 */

function testingSerialSharedDbLockPath(): string
{
    return storage_path('framework/testing-serial-shared-db.lock');
}

function acquireTestingSerialSharedDbLock(): void
{
    $path = testingSerialSharedDbLockPath();
    $directory = dirname($path);

    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $handle = fopen($path, 'c+');

    if ($handle === false) {
        throw new RuntimeException('Unable to open testing serial shared-DB lock file: '.$path);
    }

    if (! flock($handle, LOCK_EX)) {
        fclose($handle);
        throw new RuntimeException('Unable to acquire testing serial shared-DB lock: '.$path);
    }

    $GLOBALS['__dormsys_testing_serial_shared_db_lock'] = $handle;
}

function releaseTestingSerialSharedDbLock(): void
{
    $handle = $GLOBALS['__dormsys_testing_serial_shared_db_lock'] ?? null;

    if (is_resource($handle)) {
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    unset($GLOBALS['__dormsys_testing_serial_shared_db_lock']);
}
