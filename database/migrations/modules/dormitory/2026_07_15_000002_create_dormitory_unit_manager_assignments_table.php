<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dormitory_unit_manager_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->constrained('identity_users')
                ->restrictOnDelete();
            $table->foreignUuid('room_id')
                ->constrained('dormitory_rooms')
                ->restrictOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_unit_manager_assignments');
    }
};
