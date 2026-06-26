<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_departments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->uuid('manager_id')->nullable();
            $table->uuid('parent_id')->nullable();
            $table->integer('lottery_priority')->default(0);
            $table->string('status', 32);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('employee_departments', function (Blueprint $table): void {
            $table->foreign('parent_id')
                ->references('id')
                ->on('employee_departments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_departments', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
        });

        Schema::dropIfExists('employee_departments');
    }
};
