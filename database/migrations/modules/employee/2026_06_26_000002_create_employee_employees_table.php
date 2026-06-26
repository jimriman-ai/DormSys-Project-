<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_employees', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('identity_id')->unique();
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('national_code', 10)->unique();
            $table->uuid('department_id')->nullable();
            $table->date('hire_date');
            $table->integer('base_lottery_score')->default(0);
            $table->string('status', 32);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('department_id')
                ->references('id')
                ->on('employee_departments')
                ->nullOnDelete();

            $table->index('status');
        });

        Schema::table('employee_departments', function (Blueprint $table): void {
            $table->foreign('manager_id')
                ->references('id')
                ->on('employee_employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_departments', function (Blueprint $table): void {
            $table->dropForeign(['manager_id']);
        });

        Schema::dropIfExists('employee_employees');
    }
};
