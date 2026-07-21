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
        Schema::create('workflow_request_approval_step_executions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('workflow_instance_id');
            $table->string('stage', 32);
            $table->string('status', 32);
            $table->uuid('actor_identity_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestampTz('activated_at');
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();

            $table->foreign('workflow_instance_id')
                ->references('id')
                ->on('workflow_request_approval_instances')
                ->cascadeOnDelete();

            $table->index(['workflow_instance_id', 'activated_at']);
        });

        DB::statement(
            'CREATE UNIQUE INDEX workflow_req_approval_steps_one_pending_per_instance
             ON workflow_request_approval_step_executions (workflow_instance_id)
             WHERE status = \'pending\'',
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_request_approval_step_executions');
    }
};
