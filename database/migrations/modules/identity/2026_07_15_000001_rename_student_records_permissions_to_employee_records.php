<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Terminology alignment: student_records.* → employee_records.* (D-L6-2-TERM).
 * Updates permission names in place so role_has_permissions pivots stay valid.
 */
return new class extends Migration
{
    public function up(): void
    {
        $renames = [
            'student_records.read' => 'employee_records.read',
            'student_records.edit' => 'employee_records.edit',
        ];

        foreach ($renames as $from => $to) {
            DB::table('permissions')
                ->where('name', $from)
                ->where('guard_name', 'web')
                ->update(['name' => $to]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $renames = [
            'employee_records.read' => 'student_records.read',
            'employee_records.edit' => 'student_records.edit',
        ];

        foreach ($renames as $from => $to) {
            DB::table('permissions')
                ->where('name', $from)
                ->where('guard_name', 'web')
                ->update(['name' => $to]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
