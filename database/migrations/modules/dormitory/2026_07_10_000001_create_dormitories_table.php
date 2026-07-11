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
        Schema::create('dormitories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('status', 32);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('code');
            $table->index('status');
            $table->index('name');
        });

        DB::statement(
            "ALTER TABLE dormitories ADD CONSTRAINT dormitories_status_check CHECK (status IN ('available', 'unavailable', 'maintenance', 'inactive'))",
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitories');
    }
};
