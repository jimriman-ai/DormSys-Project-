<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

use App\Modules\Lottery\Domain\Exceptions\LotteryValidationException;
use App\Modules\Lottery\Domain\Exceptions\ScoringConfigNotFoundException;
use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LotteryScoringConfigReader
{
    public const string SETTINGS_KEY = 'lottery.scoring.config';

    public function load(): ScoringConfig
    {
        if (! Schema::hasTable('settings')) {
            throw new ScoringConfigNotFoundException('Settings table is not available.');
        }

        $value = DB::table('settings')
            ->where('key', self::SETTINGS_KEY)
            ->value('value');

        if ($value === null) {
            throw new ScoringConfigNotFoundException('Lottery scoring configuration is not defined.');
        }

        $decoded = $this->decodeValue($value);

        if (! is_array($decoded)) {
            throw new LotteryValidationException('Lottery scoring configuration must be a JSON object.');
        }

        try {
            return ScoringConfig::fromArray($decoded);
        } catch (LotteryValidationException $exception) {
            throw new LotteryValidationException(
                'Lottery scoring configuration is invalid: '.$exception->getMessage(),
                0,
                $exception,
            );
        }
    }

    private function decodeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }
}
