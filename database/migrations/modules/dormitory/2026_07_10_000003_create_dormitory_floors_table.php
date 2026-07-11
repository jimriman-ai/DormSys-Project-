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
        Schema::create('dormitory_floors', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('building_id');
            $table->string('label');
            $table->string('status', 32);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('building_id')
                ->references('id')
                ->on('dormitory_buildings')
                ->restrictOnDelete();

            $table->unique(['building_id', 'label']);
            $table->index('building_id');
            $table->index('status');
        });

        DB::statement(
            "ALTER TABLE dormitory_floors ADD CONSTRAINT dormitory_floors_status_check CHECK (status IN ('available', 'unavailable', 'maintenance', 'inactive'))",
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_floors');
    }
};
