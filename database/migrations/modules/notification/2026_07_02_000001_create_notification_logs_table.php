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
        Schema::create('notification_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('correlation_id', 128);
            $table->string('notification_type', 64);
            $table->uuid('recipient_employee_id');
            $table->string('title', 255);
            $table->text('message');
            $table->string('entity_type', 64)->nullable();
            $table->uuid('entity_id')->nullable();
            $table->string('deep_link_route', 255)->nullable();
            $table->string('source_context', 32);
            $table->string('priority', 16);
            $table->timestampTz('read_at')->nullable();
            $table->timestampTz('archived_at')->nullable();
            $table->string('delivery_status', 16);
            $table->string('skip_reason', 64)->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['correlation_id', 'recipient_employee_id', 'notification_type'],
                'notification_logs_dedup_uniq',
            );
            $table->index(
                ['recipient_employee_id', 'archived_at', 'created_at'],
                'notification_logs_inbox_idx',
            );
        });

        DB::statement(
            'CREATE INDEX notification_logs_unread_idx ON notification_logs (recipient_employee_id, read_at) WHERE read_at IS NULL',
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
