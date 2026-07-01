<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE allocations
            ADD CONSTRAINT allocations_person_date_range_exclusion
            EXCLUDE USING gist (
                person_id WITH =,
                date_range WITH &&
            )
            WHERE (status = 'active')
            SQL);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE allocations DROP CONSTRAINT IF EXISTS allocations_person_date_range_exclusion');
    }
};
