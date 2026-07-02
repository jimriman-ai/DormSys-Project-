<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_lifecycle_transitions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('voucher_id');
            $table->string('from_state', 32)->nullable();
            $table->string('to_state', 32);
            $table->string('correlation_id');
            $table->timestampTz('occurred_at');
            $table->jsonb('payload');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('voucher_id');
            $table->index('correlation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_lifecycle_transitions');
    }
};
