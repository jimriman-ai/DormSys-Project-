<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reporting_actor_activity_summaries', function (Blueprint $table): void {
            $table->jsonb('distinct_entity_refs')->default('[]')->after('distinct_event_types');
        });

        Schema::table('reporting_audit_window_aggregates', function (Blueprint $table): void {
            $table->jsonb('distinct_entity_refs')->default('[]')->after('distinct_actor_count');
            $table->jsonb('distinct_actor_refs')->default('[]')->after('distinct_entity_refs');
        });
    }

    public function down(): void
    {
        Schema::table('reporting_audit_window_aggregates', function (Blueprint $table): void {
            $table->dropColumn(['distinct_entity_refs', 'distinct_actor_refs']);
        });

        Schema::table('reporting_actor_activity_summaries', function (Blueprint $table): void {
            $table->dropColumn('distinct_entity_refs');
        });
    }
};
