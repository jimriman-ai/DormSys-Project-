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
        DB::statement('ALTER TABLE dormitory_beds DROP CONSTRAINT IF EXISTS dormitory_beds_occupancy_check');
        DB::statement(
            "ALTER TABLE dormitory_beds ADD CONSTRAINT dormitory_beds_occupancy_check CHECK (physical_occupancy_state IN ('vacant', 'reserved', 'occupied'))",
        );

        Schema::table('dormitory_beds', function (Blueprint $table): void {
            $table->uuid('last_signal_reference_id')->nullable()->after('physical_occupancy_state');
        });
    }

    public function down(): void
    {
        Schema::table('dormitory_beds', function (Blueprint $table): void {
            $table->dropColumn('last_signal_reference_id');
        });

        DB::statement('ALTER TABLE dormitory_beds DROP CONSTRAINT IF EXISTS dormitory_beds_occupancy_check');
        DB::statement(
            "ALTER TABLE dormitory_beds ADD CONSTRAINT dormitory_beds_occupancy_check CHECK (physical_occupancy_state IN ('vacant', 'occupied'))",
        );
    }
};
