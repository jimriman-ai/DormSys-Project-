<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_approvals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('request_id');
            $table->string('stage', 32);
            $table->string('decision', 32);
            $table->uuid('approver_id');
            $table->text('reason')->nullable();
            $table->timestamp('decided_at');
            $table->timestamp('created_at');

            $table->foreign('request_id')
                ->references('id')
                ->on('requests')
                ->cascadeOnDelete();

            $table->index('request_id');
            $table->index(['request_id', 'decided_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_approvals');
    }
};
