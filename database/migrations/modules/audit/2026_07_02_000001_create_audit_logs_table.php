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
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('correlation_id', 191);
            $table->string('event_type', 64);
            $table->string('entity_type', 64);
            $table->uuid('entity_id');
            $table->string('actor_type', 16);
            $table->string('actor_id', 128);
            $table->string('source_context', 32);
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->string('payload_hash', 64);
            $table->timestampTz('occurred_at');
            $table->timestampTz('archived_at')->nullable();
            $table->timestampTz('created_at');

            $table->unique('correlation_id', 'audit_logs_correlation_uniq');
            $table->index(['entity_type', 'entity_id', 'occurred_at'], 'audit_logs_entity_idx');
            $table->index(['actor_type', 'actor_id', 'occurred_at'], 'audit_logs_actor_idx');
            $table->index(['event_type', 'occurred_at'], 'audit_logs_event_idx');
        });

        DB::statement(
            'CREATE INDEX audit_logs_active_idx ON audit_logs (archived_at, occurred_at DESC) WHERE archived_at IS NULL',
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
