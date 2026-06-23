<?php

declare(strict_types=1);

namespace Tests\Feature\Foundation;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    public function test_postgresql_connection_is_available(): void
    {
        $pdo = DB::connection()->getPdo();

        $this->assertNotNull($pdo);
        $this->assertSame('pgsql', DB::connection()->getDriverName());
    }
}
