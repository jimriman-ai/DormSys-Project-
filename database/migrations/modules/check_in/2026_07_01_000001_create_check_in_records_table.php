<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_in_records', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('allocation_id');
            $table->timestampTz('checked_in_at');
            $table->timestampTz('checked_out_at')->nullable();
            $table->uuid('operator_id');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_in_records');
    }
};
