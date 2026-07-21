<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_request_approval_instances', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('request_id');
            $table->string('status', 32);
            $table->uuid('stage1_approver_identity_id')->nullable();
            $table->string('current_stage', 32)->nullable();
            $table->timestampTz('started_at');
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();

            $table->index(['request_id', 'status']);
        });

        // Soft UUID request_id only — no cross-module FK (WP-WF-03 / OD design).
        DB::statement(
            'CREATE UNIQUE INDEX workflow_req_approval_instances_one_running_per_request
             ON workflow_request_approval_instances (request_id)
             WHERE status = \'running\'',
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_request_approval_instances');
    }
};
