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
        Schema::create('dormitory_rooms', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('floor_id');
            $table->string('code');
            $table->string('name');
            $table->integer('capacity_total');
            $table->string('status', 32);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('floor_id')
                ->references('id')
                ->on('dormitory_floors')
                ->restrictOnDelete();

            $table->unique(['floor_id', 'code']);
            $table->index('floor_id');
            $table->index('status');
        });

        DB::statement(
            'ALTER TABLE dormitory_rooms ADD CONSTRAINT dormitory_rooms_capacity_total_non_negative CHECK (capacity_total >= 0)',
        );
        DB::statement(
            "ALTER TABLE dormitory_rooms ADD CONSTRAINT dormitory_rooms_status_check CHECK (status IN ('available', 'unavailable', 'maintenance', 'inactive'))",
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_rooms');
    }
};
