<?php

declare(strict_types=1);

namespace App\Modules\System\Infrastructure\Settings;

use App\Modules\System\Application\Contracts\SettingsReadContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Query-builder implementation of SettingsReadContract (WP-SYS-01). No Eloquent model.
 */
final class QueryBuilderSettingsReader implements SettingsReadContract
{
    public function getValue(string $key): mixed
    {
        if (! Schema::hasTable('settings')) {
            return null;
        }

        return DB::table('settings')
            ->where('key', $key)
            ->value('value');
    }
}
