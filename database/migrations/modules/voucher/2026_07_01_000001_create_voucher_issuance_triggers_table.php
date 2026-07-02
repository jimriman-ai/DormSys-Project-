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
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');

        Schema::create('voucher_issuance_triggers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('correlation_id');
            $table->uuid('employee_id');
            $table->uuid('dormitory_id')->nullable();
            $table->uuid('request_id')->nullable();
            $table->string('source', 32);
            $table->string('status', 32);
            $table->timestampTz('issuance_path_completed_at')->nullable();
            $table->uuid('superseded_by_trigger_id')->nullable();
            $table->jsonb('upstream_facts');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('correlation_id');
            $table->index(['employee_id', 'status']);
        });

        DB::statement('ALTER TABLE voucher_issuance_triggers ADD COLUMN stay_period daterange NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_issuance_triggers');
    }
};
