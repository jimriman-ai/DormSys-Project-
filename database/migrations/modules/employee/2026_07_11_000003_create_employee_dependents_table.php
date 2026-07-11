<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_dependents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('relationship', 32);
            $table->integer('age')->nullable();
            $table->string('national_code', 10)->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employee_employees')
                ->cascadeOnDelete();

            $table->index('employee_id');
            $table->index('relationship');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_dependents');
    }
};
