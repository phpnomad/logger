<?php

namespace PHPNomad\Logger\Traits;

use Exception;
use PHPNomad\Logger\Enums\LoggerLevel;
use PHPNomad\Logger\Interfaces\LoggerStrategy;

trait CanLogException
{
    protected LoggerStrategy $loggerStrategy;

    public function logException(Exception $e, string $message = '', array $context = [], $level = null)
    {
        if(!$level){
            $level = LoggerLevel::Critical;
        }

        $this->loggerStrategy->$level(implode(' - ', [$message, $e->getMessage()]), $context);
    }
}