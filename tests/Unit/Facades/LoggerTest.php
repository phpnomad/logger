<?php

namespace Phoenix\Logger\Tests\Unit\Facades;

use Generator;
use Mockery;
use Mockery\MockInterface;
use Phoenix\Logger\Facades\Logger;
use Phoenix\Logger\Interfaces\LoggerStrategy;
use Phoenix\Logger\Tests\TestCase;
use Phoenix\Tests\Traits\WithInaccessibleMethods;
use ReflectionException;

class LoggerTest extends TestCase
{
    use WithInaccessibleMethods;

    /**
     * @var Logger&MockInterface
     */
    protected $facade;

    /**
     * @var LoggerStrategy&MockInterface
     */
    protected $containedMock;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->facade = Mockery::mock(Logger::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->containedMock = Mockery::mock(LoggerStrategy::class);

        $this->facade->allows('instance->getContainedInstance')
            ->andReturn($this->containedMock);
    }

    /**
     * @covers       \Phoenix\Logger\Facades\Event::load
     * @dataProvider providerForMethods
     */
    public function testCanProvideMethods(string $method): void
    {
        $message = 'message';
        $context = ['foo'];

        $this->containedMock->expects($method)
            ->once()
            ->with($message, $context);

        $this->facade::{$method}($message, $context);
    }

    public function providerForMethods(): Generator
    {
        yield ['emergency'];
        yield ['alert'];
        yield ['critical'];
        yield ['error'];
        yield ['warning'];
        yield ['notice'];
        yield ['info'];
        yield ['debug'];
    }

    /**
     * @covers \Phoenix\Logger\Facades\Logger::abstractInstance
     * @throws ReflectionException
     */
    public function testAbstractInstanceMatchesExpected(): void
    {
        $actual = $this->callInaccessibleMethod(new Logger(), 'abstractInstance');

        $this->assertEquals(LoggerStrategy::class, $actual);
    }
}