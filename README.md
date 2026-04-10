# phpnomad/logger

[![Latest Version](https://img.shields.io/packagist/v/phpnomad/logger.svg)](https://packagist.org/packages/phpnomad/logger)
[![Total Downloads](https://img.shields.io/packagist/dt/phpnomad/logger.svg)](https://packagist.org/packages/phpnomad/logger)
[![PHP Version](https://img.shields.io/packagist/php-v/phpnomad/logger.svg)](https://packagist.org/packages/phpnomad/logger)
[![License](https://img.shields.io/packagist/l/phpnomad/logger.svg)](https://packagist.org/packages/phpnomad/logger)

`phpnomad/logger` defines the logging contract used throughout PHPNomad applications. It ships the `LoggerStrategy` interface, the `CanLogException` trait, and the `LoggerLevel` constants, and nothing else. Implementations live in other packages or in your own application code, so you can swap logging destinations without touching the code that calls the logger.

## Installation

```bash
composer require phpnomad/logger
```

## Overview

The package provides a small, focused set of building blocks:

- `LoggerStrategy` interface with eight level methods (`emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug`) plus `logException` for structured exception logging.
- `CanLogException` trait that implements `logException` by attaching the exception to context and dispatching to the configured level method (defaults to `critical`).
- `LoggerLevel` constants for the eight level strings, matching PSR-3 conventions.
- Zero runtime dependencies. The package is pure abstraction.
- Pairs with `phpnomad/core`, which ships an event-broadcasting strategy and a static `Logger` facade. Transports like `phpnomad/sentry-integration` subscribe to those events in production builds.

## Usage

Inject `LoggerStrategy` and call the appropriate level:

```php
use PHPNomad\Logger\Interfaces\LoggerStrategy;

class OrderService
{
    public function __construct(private LoggerStrategy $logger) {}

    public function process(Order $order): void
    {
        $this->logger->info('Processing order', ['order_id' => $order->getId()]);
    }
}
```

## Documentation

Full documentation for the logger package and the rest of PHPNomad lives at [phpnomad.com](https://phpnomad.com).

## License

MIT. See [LICENSE.txt](LICENSE.txt).
