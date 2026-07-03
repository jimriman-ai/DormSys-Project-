<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting_audit_window_aggregates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->timestampTz('window_start');
            $table->timestampTz('window_end');
            $table->string('granularity', 16);
            $table->string('event_type', 64)->nullable();
            $table->string('source_context', 32)->nullable();
            $table->string('actor_type', 16)->nullable();
            $table->string('entity_type', 64)->nullable();
            $table->string('archive_visibility_tier', 32);
            $table->unsignedInteger('event_count');
            $table->unsignedInteger('distinct_entity_count');
            $table->unsignedInteger('distinct_actor_count');
            $table->jsonb('top_event_types')->nullable();
            $table->timestampTz('refreshed_at');
            $table->string('projection_version', 32);

            $table->unique(
                [
                    'window_start',
                    'window_end',
                    'granularity',
                    'event_type',
                    'source_context',
                    'actor_type',
                    'entity_type',
                    'archive_visibility_tier',
                ],
                'reporting_audit_window_agg_dim_uniq',
            );
            $table->index(
                ['window_start', 'window_end', 'granularity'],
                'reporting_audit_window_agg_window_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_audit_window_aggregates');
    }
};
