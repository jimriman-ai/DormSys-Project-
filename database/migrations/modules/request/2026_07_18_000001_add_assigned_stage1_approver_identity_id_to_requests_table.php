<?php

declare(strict_types=1);

/**
 * [PERMIT-ID: IMPL-PERMIT-01] §2.1 — Stage-1 assigned approver identity snapshot column.
 * IMP-Q-02 A / OQ-AUTH-03 B / DGAP-06 V1.
 * FK → identity_users with restrictOnDelete (Lead-authorized in IMPL-PERMIT-01).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table): void {
            $table->uuid('assigned_stage1_approver_identity_id')->nullable()->after('employee_id');

            $table->foreign('assigned_stage1_approver_identity_id')
                ->references('id')
                ->on('identity_users')
                ->restrictOnDelete();

            $table->index('assigned_stage1_approver_identity_id');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table): void {
            $table->dropForeign(['assigned_stage1_approver_identity_id']);
            $table->dropColumn('assigned_stage1_approver_identity_id');
        });
    }
};
