<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_eligibility_outcomes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trigger_id')->unique();
            $table->string('correlation_id');
            $table->uuid('employee_id');
            $table->uuid('dormitory_id')->nullable();
            $table->uuid('request_id')->nullable();
            $table->string('outcome', 32);
            $table->jsonb('reason_codes');
            $table->text('rationale');
            $table->timestampTz('evaluated_at');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('correlation_id');
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_eligibility_outcomes');
    }
};
