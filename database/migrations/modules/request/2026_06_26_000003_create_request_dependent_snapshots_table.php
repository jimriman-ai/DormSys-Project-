<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_dependent_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('request_id');
            $table->uuid('source_dependent_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('relationship', 64);
            $table->string('national_code', 10)->nullable();
            $table->timestamp('captured_at');
            $table->timestamp('created_at');

            $table->foreign('request_id')
                ->references('id')
                ->on('requests')
                ->cascadeOnDelete();

            $table->index('request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_dependent_snapshots');
    }
};
