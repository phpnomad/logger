<?php

namespace Phoenix\Logger\Traits;

use Exception;
use Phoenix\Logger\Enums\LoggerLevel;
use Phoenix\Logger\Interfaces\LoggerStrategy;

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