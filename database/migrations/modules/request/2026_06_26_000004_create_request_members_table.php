<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_members', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('request_id');
            $table->uuid('employee_id');
            $table->boolean('is_leader')->default(false);
            $table->timestamp('created_at');

            $table->foreign('request_id')
                ->references('id')
                ->on('requests')
                ->cascadeOnDelete();

            $table->index('request_id');
            $table->index(['request_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_members');
    }
};
