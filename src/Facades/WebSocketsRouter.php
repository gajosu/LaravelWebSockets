<?php

namespace Gajosu\LaravelWebSockets\Facades;

use Illuminate\Support\Facades\Facade;

/** @see \Gajosu\LaravelWebSockets\Server\Router */
class WebSocketsRouter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'websockets.router';
    }
}
