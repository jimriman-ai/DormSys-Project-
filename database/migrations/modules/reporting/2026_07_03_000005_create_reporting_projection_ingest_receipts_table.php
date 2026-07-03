<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting_projection_ingest_receipts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('projection_family', 32);
            $table->uuid('source_audit_log_id');
            $table->string('archive_visibility_tier', 32);
            $table->timestampTz('ingested_at');

            $table->unique(
                ['projection_family', 'source_audit_log_id', 'archive_visibility_tier'],
                'reporting_proj_ingest_receipt_uniq',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_projection_ingest_receipts');
    }
};
