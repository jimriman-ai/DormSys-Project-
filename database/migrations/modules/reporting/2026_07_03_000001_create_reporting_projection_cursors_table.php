<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting_projection_cursors', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('projection_family', 32);
            $table->string('archive_visibility_tier', 32);
            $table->uuid('last_source_audit_log_id')->nullable();
            $table->timestampTz('last_occurred_at')->nullable();
            $table->string('projection_version', 32);
            $table->timestampTz('refreshed_at')->nullable();
            $table->string('refresh_mode', 32);
            $table->string('status', 16);
            $table->text('last_error')->nullable();
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->unique(
                ['projection_family', 'archive_visibility_tier'],
                'reporting_projection_cursors_family_tier_uniq',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_projection_cursors');
    }
};
