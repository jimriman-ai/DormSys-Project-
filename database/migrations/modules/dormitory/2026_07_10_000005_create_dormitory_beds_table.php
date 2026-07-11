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
        Schema::create('dormitory_beds', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('room_id');
            $table->string('label');
            $table->string('status', 32);
            $table->string('physical_occupancy_state', 32);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('room_id')
                ->references('id')
                ->on('dormitory_rooms')
                ->restrictOnDelete();

            $table->unique(['room_id', 'label']);
            $table->index('room_id');
            $table->index('status');
            $table->index('physical_occupancy_state');
            $table->index(['status', 'physical_occupancy_state'], 'dormitory_beds_availability_idx');
        });

        DB::statement(
            "ALTER TABLE dormitory_beds ADD CONSTRAINT dormitory_beds_status_check CHECK (status IN ('available', 'unavailable', 'maintenance', 'inactive'))",
        );
        DB::statement(
            "ALTER TABLE dormitory_beds ADD CONSTRAINT dormitory_beds_occupancy_check CHECK (physical_occupancy_state IN ('vacant', 'occupied'))",
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_beds');
    }
};
