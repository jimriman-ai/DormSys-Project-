<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Shared\Infrastructure\Uuid\UuidGenerator;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class UuidGeneratorTest extends TestCase
{
    #[Test]
    public function it_generates_a_valid_uuidv7_string(): void
    {
        $id = UuidGenerator::uuid7();

        $this->assertTrue(Uuid::isValid($id));
        $this->assertSame(7, Uuid::fromString($id)->getFields()->getVersion());
    }
}
