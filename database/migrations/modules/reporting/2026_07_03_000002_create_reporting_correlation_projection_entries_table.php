<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting_correlation_projection_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('correlation_id', 191);
            $table->uuid('source_audit_log_id');
            $table->timestampTz('occurred_at');
            $table->string('entity_type', 64);
            $table->uuid('entity_id');
            $table->string('actor_type', 16);
            $table->string('actor_id', 128);
            $table->string('event_type', 64);
            $table->string('source_context', 32);
            $table->string('archive_visibility_tier', 32);
            $table->timestampTz('ingested_at');

            $table->unique(
                ['correlation_id', 'source_audit_log_id', 'archive_visibility_tier'],
                'reporting_corr_proj_entry_uniq',
            );
            $table->index(
                ['correlation_id', 'occurred_at'],
                'reporting_corr_proj_correlation_idx',
            );
            $table->index(
                ['source_audit_log_id'],
                'reporting_corr_proj_source_audit_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_correlation_projection_entries');
    }
};
