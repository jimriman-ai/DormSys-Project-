<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Q-EMP-DORM Option B / WP-DASH-G02-R1: employee ↔ dormitory assignment.
 * user_id FK: CONSTRAINED_IDENTITY → identity_users, ON DELETE RESTRICT.
 * Independent of dormitory_manager_assignments / dormitory_unit_manager_assignments.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dormitory_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->constrained('identity_users')
                ->restrictOnDelete();
            $table->foreignUuid('dormitory_id')
                ->constrained('dormitories')
                ->restrictOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('revoked_at')->nullable();
        });

        DB::statement(
            'CREATE UNIQUE INDEX dormitory_assignments_user_dormitory_active_uidx
             ON dormitory_assignments (user_id, dormitory_id)
             WHERE revoked_at IS NULL',
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_assignments');
    }
};
