<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocation_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('allocation_id');
            $table->uuid('bed_id');
            $table->unsignedSmallInteger('sequence');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('allocation_id')
                ->references('id')
                ->on('allocations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('allocation_items', function (Blueprint $table): void {
            $table->dropForeign(['allocation_id']);
        });

        Schema::dropIfExists('allocation_items');
    }
};
