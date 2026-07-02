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
        Schema::create('vouchers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('eligibility_outcome_id')->unique();
            $table->uuid('trigger_id');
            $table->string('correlation_id');
            $table->uuid('employee_id');
            $table->uuid('dormitory_id')->nullable();
            $table->uuid('request_id')->nullable();
            $table->string('upstream_source', 32);
            $table->string('code', 32)->unique();
            $table->string('lifecycle_state', 32);
            $table->timestampTz('validity_start');
            $table->timestampTz('validity_end');
            $table->timestampTz('issued_at');
            $table->timestampTz('archived_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('employee_id');
            $table->index('correlation_id');
            $table->index('lifecycle_state');
        });

        DB::statement('ALTER TABLE vouchers ADD COLUMN stay_period daterange NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
