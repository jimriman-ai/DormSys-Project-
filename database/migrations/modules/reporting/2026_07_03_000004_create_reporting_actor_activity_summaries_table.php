<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting_actor_activity_summaries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('actor_type', 16);
            $table->string('actor_id', 128);
            $table->timestampTz('window_start');
            $table->timestampTz('window_end');
            $table->string('granularity', 16);
            $table->unsignedInteger('event_count');
            $table->jsonb('distinct_event_types');
            $table->unsignedInteger('distinct_entities_touched');
            $table->string('archive_visibility_tier', 32);
            $table->timestampTz('refreshed_at');
            $table->string('projection_version', 32);

            $table->unique(
                [
                    'actor_type',
                    'actor_id',
                    'window_start',
                    'window_end',
                    'granularity',
                    'archive_visibility_tier',
                ],
                'reporting_actor_activity_summ_uniq',
            );
            $table->index(
                ['actor_type', 'actor_id', 'window_start'],
                'reporting_actor_activity_actor_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_actor_activity_summaries');
    }
};
