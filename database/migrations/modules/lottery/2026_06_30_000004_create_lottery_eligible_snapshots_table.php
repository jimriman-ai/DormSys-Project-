<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_eligible_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->json('payload');
            $table->string('random_seed');
            $table->json('scoring_config');
            $table->string('scoring_config_version')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('program_id')
                ->references('id')
                ->on('lottery_programs');

            $table->unique('program_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_eligible_snapshots');
    }
};
