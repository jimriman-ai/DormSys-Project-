<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_results', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->uuid('registration_id');
            $table->unsignedInteger('rank');
            $table->string('outcome', 32);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('program_id')
                ->references('id')
                ->on('lottery_programs');

            $table->foreign('registration_id')
                ->references('id')
                ->on('lottery_registrations');

            $table->unique(['program_id', 'registration_id']);
            $table->index(['program_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_results');
    }
};
