<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Events\BaseEvent;
use App\Support\Exceptions\ValidationException;
use App\Support\ValueObjects\Identity\NationalCode;
use App\Support\ValueObjects\Jalali\JalaliDate;
use App\Support\ValueObjects\Jalali\JalaliDateRange;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class SupportKernelTest extends TestCase
{
    #[Test]
    public function it_creates_jalali_date_from_gregorian_datetime(): void
    {
        $date = JalaliDate::fromDateTime('2026-06-22 12:00:00');

        $this->assertSame(1405, $date->year);
        $this->assertSame(4, $date->month);
        $this->assertSame(1, $date->day);
        $this->assertSame('1405/04/01', $date->toString());
    }

    #[Test]
    public function it_parses_jalali_date_from_string(): void
    {
        $date = JalaliDate::fromString('1405-04-01');

        $this->assertTrue($date->equals(JalaliDate::fromString('1405/04/01')));
    }

    #[Test]
    public function it_rejects_invalid_jalali_date_string(): void
    {
        $this->expectException(ValidationException::class);

        JalaliDate::fromString('invalid');
    }

    #[Test]
    public function it_builds_valid_jalali_date_range(): void
    {
        $range = JalaliDateRange::fromStrings('1405/04/01', '1405/04/15');

        $this->assertTrue($range->contains(JalaliDate::fromString('1405/04/10')));
        $this->assertFalse($range->contains(JalaliDate::fromString('1405/04/20')));
    }

    #[Test]
    public function it_rejects_inverted_jalali_date_range(): void
    {
        $this->expectException(ValidationException::class);

        new JalaliDateRange(
            start: JalaliDate::fromString('1405/04/20'),
            end: JalaliDate::fromString('1405/04/01'),
        );
    }

    #[Test]
    public function it_validates_iranian_national_code_checksum(): void
    {
        $code = NationalCode::fromString('0499370899');

        $this->assertSame('0499370899', $code->toString());
        $this->assertTrue(NationalCode::isValid('0499370899'));
        $this->assertFalse(NationalCode::isValid('1111111111'));
    }

    #[Test]
    public function it_rejects_invalid_national_code(): void
    {
        $this->expectException(ValidationException::class);

        NationalCode::fromString('1234567890');
    }

    #[Test]
    public function it_raises_base_event_with_uuidv7_metadata(): void
    {
        $event = TestFoundationEvent::raise(
            aggregateId: Uuid::uuid7()->toString(),
            payload: ['ping' => true],
        );

        $this->assertTrue(Uuid::isValid($event->eventId));
        $this->assertSame(7, Uuid::fromString($event->eventId)->getFields()->getVersion());
        $this->assertSame(['ping' => true], $event->payload);
        $this->assertArrayHasKey('event_type', $event->jsonSerialize());
    }
}

final class TestFoundationEvent extends BaseEvent {}
