<?php

declare(strict_types=1);

namespace Tests\Support;

use Mockery;
use Mockery\Expectation;
use Mockery\MockInterface;
use RuntimeException;

final class MockeryTest
{
    /**
     * @template T of object
     *
     * @param  class-string<T>  $interface
     * @return MockInterface&T
     */
    public static function mock(string $interface): MockInterface
    {
        /** @var MockInterface&T $mock */
        $mock = Mockery::mock($interface);

        return $mock;
    }

    public static function expect(MockInterface $mock, string $method): Expectation
    {
        $expectation = $mock->shouldReceive($method);

        if (! $expectation instanceof Expectation) {
            throw new RuntimeException('Mockery shouldReceive did not return an Expectation.');
        }

        return $expectation;
    }

    public static function expectOnce(MockInterface $mock, string $method): Expectation
    {
        return self::expect($mock, $method)->once();
    }
}
