<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DG-SETTINGS-01 / WP-DEBT-04 — production settings table (System ownership).
 * Schema must match test Blueprint (LotteryFeatureSupport et al.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->json('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
