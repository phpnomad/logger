<?php

namespace Phoenix\Logger\Facades;

use Phoenix\Logger\Interfaces\LoggerStrategy;
use Phoenix\Facade\Abstracts\Facade;
use Phoenix\Singleton\Traits\WithInstance;

/**
 * @extends Facade<LoggerStrategy>
 */
class Logger extends Facade
{
    use WithInstance;

    /**
     * @param string $message
     * @param mixed[] $context
     * @return void
     */
    public static function emergency(string $message, array $context = []): void{
        static::instance()->getContainedInstance()->emergency($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     * @return void
     */
    public static function alert(string $message, array $context = []): void{
        static::instance()->getContainedInstance()->alert($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     * @return void
     */
    public static function critical(string $message, array $context = []): void{
        static::instance()->getContainedInstance()->critical($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     * @return void
     */
    public static function error(string $message, array $context = []): void{
        static::instance()->getContainedInstance()->error($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     * @return void
     */
    public static function warning(string $message, array $context = []): void{
        static::instance()->getContainedInstance()->warning($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     * @return void
     */
    public static function notice(string $message, array $context = []): void{
        static::instance()->getContainedInstance()->notice($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     * @return void
     */
    public static function info(string $message, array $context = []): void{
        static::instance()->getContainedInstance()->info($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     * @return void
     */
    public static function debug(string $message, array $context = []): void{
        static::instance()->getContainedInstance()->debug($message, $context);
    }

    protected function abstractInstance(): string
    {
        return LoggerStrategy::class;
    }
}