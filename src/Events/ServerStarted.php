<?php

namespace Gajosu\LaravelWebSockets\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServerStarted
{
    use Dispatchable, SerializesModels;

    public function __construct()
    {
    }
}
