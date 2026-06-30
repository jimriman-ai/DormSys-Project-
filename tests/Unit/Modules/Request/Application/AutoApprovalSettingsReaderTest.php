<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Request\Application;

use App\Modules\Request\Application\Services\AutoApprovalSettingsReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class AutoApprovalSettingsReaderTest extends TestCase
{
    #[Test]
    #[DataProvider('booleanValueProvider')]
    public function it_normalizes_settings_values_to_boolean(mixed $value, bool $expected): void
    {
        $reader = new AutoApprovalSettingsReader();
        $method = new ReflectionMethod(AutoApprovalSettingsReader::class, 'normalizeBool');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($reader, $value));
    }

    /**
     * @return array<string, array{0: mixed, 1: bool}>
     */
    public static function booleanValueProvider(): array
    {
        return [
            'native true' => [true, true],
            'native false' => [false, false],
            'json boolean true' => ['true', true],
            'json boolean false' => ['false', false],
            'json numeric one' => ['1', true],
            'json numeric zero' => ['0', false],
            'native integer one' => [1, true],
            'native integer zero' => [0, false],
            'string on' => ['on', true],
            'string off' => ['off', false],
        ];
    }
}
