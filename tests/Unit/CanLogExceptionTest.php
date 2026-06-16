<?php

namespace PHPNomad\Logger\Tests\Unit;

use Exception;
use PHPNomad\Logger\Enums\LoggerLevel;
use PHPNomad\Logger\Tests\TestCase;
use PHPNomad\Logger\Traits\CanLogException;

class CanLogExceptionTest extends TestCase
{
    /**
     * Builds a logger that records the level, message, and context of each call.
     */
    protected function makeLogger(): object
    {
        return new class () {
            use CanLogException;

            /** @var array{level:string,message:string,context:array}|null */
            public ?array $logged = null;

            public function __call(string $name, array $arguments): void
            {
                $this->logged = [
                    'level' => $name,
                    'message' => $arguments[0] ?? '',
                    'context' => $arguments[1] ?? [],
                ];
            }
        };
    }

    public function testLogsAtCriticalByDefault(): void
    {
        $logger = $this->makeLogger();

        $logger->logException(new Exception('boom'));

        $this->assertSame(LoggerLevel::Critical, $logger->logged['level']);
    }

    public function testRespectsExplicitLevel(): void
    {
        $logger = $this->makeLogger();

        $logger->logException(new Exception('boom'), '', [], LoggerLevel::Warning);

        $this->assertSame(LoggerLevel::Warning, $logger->logged['level']);
    }

    public function testKeepsExceptionInContext(): void
    {
        $logger = $this->makeLogger();
        $e = new Exception('boom');

        $logger->logException($e);

        $this->assertSame($e, $logger->logged['context']['exception']);
    }

    public function testPreservesProvidedContext(): void
    {
        $logger = $this->makeLogger();

        $logger->logException(new Exception('boom'), '', ['orgId' => 7]);

        $this->assertSame(7, $logger->logged['context']['orgId']);
    }

    public function testIncludesPrefixMessageAndExceptionMessage(): void
    {
        $logger = $this->makeLogger();

        $logger->logException(new Exception('boom'), 'while saving');

        $this->assertStringContainsString('while saving', $logger->logged['message']);
        $this->assertStringContainsString('boom', $logger->logged['message']);
    }

    public function testWalksChainedCauseIntoMessage(): void
    {
        $logger = $this->makeLogger();

        // Mirrors the datastore backend: a stable, client-safe outer message
        // with the real driver error chained as the previous exception.
        $cause = new Exception('SQLSTATE[42S22]: Column not found: 1054 Unknown column "foo"');
        $outer = new Exception('Failed to execute query.', 0, $cause);

        $logger->logException($outer);

        $message = $logger->logged['message'];
        $this->assertStringContainsString('Failed to execute query.', $message);
        $this->assertStringContainsString('Unknown column "foo"', $message);
    }

    public function testWalksMultiLevelChain(): void
    {
        $logger = $this->makeLogger();

        $root = new Exception('root cause');
        $mid = new Exception('mid layer', 0, $root);
        $outer = new Exception('outer', 0, $mid);

        $logger->logException($outer);

        $message = $logger->logged['message'];
        $this->assertStringContainsString('outer', $message);
        $this->assertStringContainsString('mid layer', $message);
        $this->assertStringContainsString('root cause', $message);
    }

    public function testEmptyPrefixDoesNotProduceLeadingSeparator(): void
    {
        $logger = $this->makeLogger();

        $logger->logException(new Exception('boom'));

        $this->assertSame('boom', $logger->logged['message']);
    }

    public function testDoesNotRepeatCauseAlreadyEmbeddedInWrapperMessage(): void
    {
        $logger = $this->makeLogger();

        // Mirrors QueryStrategy: the wrapper embeds the driver message in its
        // own text AND chains the driver exception as the cause.
        $driverMessage = 'SQLSTATE[42S22]: Unknown column "foo"';
        $cause = new Exception($driverMessage);
        $outer = new Exception('Invalid query: ' . $driverMessage, 0, $cause);

        $logger->logException($outer);

        $this->assertSame(
            1,
            substr_count($logger->logged['message'], $driverMessage),
            'The chained cause should not be logged twice when the wrapper already embeds it.'
        );
    }

    public function testBoundsPathologicallyDeepChains(): void
    {
        $logger = $this->makeLogger();

        $e = new Exception('level-0');
        for ($i = 1; $i <= 50; $i++) {
            $e = new Exception('level-' . $i, 0, $e);
        }

        $logger->logException($e);

        // Default cap is 10 levels: the outermost is kept, deeper roots dropped.
        $this->assertStringContainsString('level-50', $logger->logged['message']);
        $this->assertStringNotContainsString('level-0', $logger->logged['message']);
        $this->assertSame(10, substr_count($logger->logged['message'], 'level-'));
    }
}
