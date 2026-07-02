<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Audit recording
    |--------------------------------------------------------------------------
    */

    'recording_enabled' => env('AUDIT_RECORDING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Synchronous recording in tests
    |--------------------------------------------------------------------------
    */

    'sync_in_tests' => env('AUDIT_SYNC_IN_TESTS', false),

    /*
    |--------------------------------------------------------------------------
    | Activity log bridge (M2)
    |--------------------------------------------------------------------------
    |
    | When disabled, Spatie activity_log rows are not forwarded to audit_logs.
    |
    */

    'activity_bridge_enabled' => env('AUDIT_ACTIVITY_BRIDGE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Retention (UD-10-03)
    |--------------------------------------------------------------------------
    */

    'retention_months' => (int) env('AUDIT_RETENTION_MONTHS', 84),

];
