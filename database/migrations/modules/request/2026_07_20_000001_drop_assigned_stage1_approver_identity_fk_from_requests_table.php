<?php

declare(strict_types=1);

/**
 * WP-REQ-01 / OQ-REQ-02 Option A — drop physical FK only.
 * Retains assigned_stage1_approver_identity_id UUID column + index.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table): void {
            $table->dropForeign(['assigned_stage1_approver_identity_id']);
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table): void {
            $table->foreign('assigned_stage1_approver_identity_id')
                ->references('id')
                ->on('identity_users')
                ->restrictOnDelete();
        });
    }
};
