<?php

namespace Gajosu\LaravelWebSockets\Facades;

use Gajosu\LaravelWebSockets\Statistics\Logger\StatisticsLogger as StatisticsLoggerInterface;
use Illuminate\Support\Facades\Facade;

/** @see \Gajosu\LaravelWebSockets\Statistics\Logger\HttpStatisticsLogger */
class StatisticsLogger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return StatisticsLoggerInterface::class;
    }
}
