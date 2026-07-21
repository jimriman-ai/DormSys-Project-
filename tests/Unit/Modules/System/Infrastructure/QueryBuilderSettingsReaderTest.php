<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\System\Infrastructure;

use App\Modules\System\Infrastructure\Settings\QueryBuilderSettingsReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class QueryBuilderSettingsReaderTest extends TestCase
{
    #[Test]
    public function it_returns_null_when_settings_table_is_absent(): void
    {
        Schema::shouldReceive('hasTable')
            ->once()
            ->with('settings')
            ->andReturn(false);

        $reader = new QueryBuilderSettingsReader;

        $this->assertNull($reader->getValue('any.key'));
    }

    #[Test]
    public function it_reads_value_via_query_builder_when_table_exists(): void
    {
        Schema::shouldReceive('hasTable')
            ->once()
            ->with('settings')
            ->andReturn(true);

        DB::shouldReceive('table')
            ->once()
            ->with('settings')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->once()
            ->with('key', 'demo.key')
            ->andReturnSelf();
        DB::shouldReceive('value')
            ->once()
            ->with('value')
            ->andReturn('stored');

        $reader = new QueryBuilderSettingsReader;

        $this->assertSame('stored', $reader->getValue('demo.key'));
    }
}
