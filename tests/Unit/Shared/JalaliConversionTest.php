<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use Morilog\Jalali\Jalalian;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JalaliConversionTest extends TestCase
{
    #[Test]
    public function it_converts_gregorian_date_to_jalali(): void
    {
        $jalali = Jalalian::fromDateTime('2026-06-22 12:00:00');

        $this->assertSame(1405, $jalali->getYear());
        $this->assertSame(4, $jalali->getMonth());
        $this->assertSame(1, $jalali->getDay());
    }
}
