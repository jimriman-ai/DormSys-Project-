<?php

declare(strict_types=1);

namespace Tests\Support;

use Mockery;
use Mockery\Expectation;
use Mockery\MockInterface;

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

    /**
     * @return Expectation
     */
    public static function expect(MockInterface $mock, string $method): mixed
    {
        /** @var Expectation $expectation */
        $expectation = $mock->shouldReceive($method);

        return $expectation;
    }

    /**
     * @return Expectation
     */
    public static function expectOnce(MockInterface $mock, string $method): mixed
    {
        return self::expect($mock, $method)->once();
    }
}
