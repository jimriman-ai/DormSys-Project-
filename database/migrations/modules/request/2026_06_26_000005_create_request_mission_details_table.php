<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_mission_details', function (Blueprint $table): void {
            $table->uuid('request_id')->primary();
            $table->string('mission_document_url')->nullable();
            $table->text('description');
            $table->timestamp('created_at');

            $table->foreign('request_id')
                ->references('id')
                ->on('requests')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_mission_details');
    }
};
