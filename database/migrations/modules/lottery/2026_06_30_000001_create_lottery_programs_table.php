<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_programs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->uuid('dormitory_id');
            $table->unsignedInteger('capacity');
            $table->timestamp('registration_starts_at');
            $table->timestamp('registration_ends_at');
            $table->string('status', 64);
            $table->string('random_seed')->nullable();
            $table->string('scoring_config_version')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('drawn_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('dormitory_id');
            $table->index('status');
            $table->index(['status', 'registration_ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_programs');
    }
};
