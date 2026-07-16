<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BL-B1-01 / RM-01: manager ↔ dormitory assignment pivot.
 * user_id FK: CONSTRAINED_IDENTITY → identity_users, ON DELETE RESTRICT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dormitory_manager_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->constrained('identity_users')
                ->restrictOnDelete();
            $table->foreignUuid('dormitory_id')
                ->constrained('dormitories')
                ->restrictOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'dormitory_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_manager_assignments');
    }
};
