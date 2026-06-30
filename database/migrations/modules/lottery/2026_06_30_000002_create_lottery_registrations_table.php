<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_registrations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->uuid('request_id');
            $table->uuid('employee_id');
            $table->decimal('weighted_score', 16, 8)->nullable();
            $table->timestamp('enrolled_at');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('program_id')
                ->references('id')
                ->on('lottery_programs');

            $table->unique(['program_id', 'request_id']);
            $table->index('employee_id');
            $table->index(['program_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_registrations');
    }
};
