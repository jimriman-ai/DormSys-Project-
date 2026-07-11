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
        Schema::create('dormitory_buildings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('dormitory_id');
            $table->string('code');
            $table->string('name');
            $table->string('status', 32);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('dormitory_id')
                ->references('id')
                ->on('dormitories')
                ->restrictOnDelete();

            $table->unique(['dormitory_id', 'code']);
            $table->index('dormitory_id');
            $table->index('status');
        });

        DB::statement(
            "ALTER TABLE dormitory_buildings ADD CONSTRAINT dormitory_buildings_status_check CHECK (status IN ('available', 'unavailable', 'maintenance', 'inactive'))",
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_buildings');
    }
};
