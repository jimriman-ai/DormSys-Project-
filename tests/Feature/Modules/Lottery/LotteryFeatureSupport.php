<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Services\LotteryScoringConfigReader;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

const LOTTERY_RESULT_READ_CONTRACT_KEYS = ['program_id', 'winners', 'reserves', 'ranks'];

/** Matches lottery_registrations.weighted_score decimal(16, 8) persistence precision. */
const LOTTERY_WEIGHTED_SCORE_DB_EPSILON = 1e-8;

function expectPersistedWeightedScore(?float $persisted, float $computed): void
{
    expect($persisted)->not->toBeNull();
    expect(abs($persisted - $computed))->toBeLessThan(LOTTERY_WEIGHTED_SCORE_DB_EPSILON);
}

function bootstrapLotteryFeatureTests(): void
{
    Carbon::setTestNow('2026-06-30 12:00:00');

    if (! Schema::hasTable('settings')) {
        Schema::create('settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->json('value');
            $table->timestamps();
        });
    }

    DB::table('settings')->updateOrInsert(
        ['key' => LotteryScoringConfigReader::SETTINGS_KEY],
        [
            'id' => UuidGenerator::uuid7(),
            'value' => json_encode([
                'version' => '1.0.0',
                'base_score_coefficient' => 1.0,
                'department_priority_coefficient' => 0.05,
                'normalization_divisor' => 100.0,
                'prng_scale' => 1.0,
            ], JSON_THROW_ON_ERROR),
            'updated_at' => now(),
            'created_at' => now(),
        ],
    );
}

function teardownLotteryFeatureTests(): void
{
    Carbon::setTestNow();
}

/**
 * @param  array<string, mixed>  $payload
 */
function assertLotteryResultReadContractShape(array $payload): void
{
    expect(array_keys($payload))->toEqual(LOTTERY_RESULT_READ_CONTRACT_KEYS);
    expect($payload['winners'])->toBeArray();
    expect($payload['reserves'])->toBeArray();
    expect($payload['ranks'])->toBeArray();

    foreach ($payload['winners'] as $row) {
        expect(array_keys($row))->toEqual(['registration_id', 'rank']);
        expect($row['registration_id'])->toBeString();
        expect($row['rank'])->toBeInt();
    }

    foreach ($payload['reserves'] as $row) {
        expect(array_keys($row))->toEqual(['registration_id', 'rank']);
        expect($row['registration_id'])->toBeString();
        expect($row['rank'])->toBeInt();
    }

    foreach ($payload['ranks'] as $row) {
        expect(array_keys($row))->toEqual(['rank', 'registration_id', 'outcome']);
        expect($row['rank'])->toBeInt();
        expect($row['registration_id'])->toBeString();
        expect($row['outcome'])->toBeIn(['winner', 'reserve']);
    }
}
