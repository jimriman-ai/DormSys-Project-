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

        Schema::create('allocations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('person_id');
            $table->uuid('bed_id');
            $table->string('method', 32);
            $table->string('status', 32);
            $table->uuid('source_request_id')->nullable();
            $table->uuid('source_lottery_result_id')->nullable();
            $table->timestampTz('released_at')->nullable();
            $table->text('release_reason')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE allocations ADD COLUMN date_range daterange NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};
