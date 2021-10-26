<?php

namespace Gajosu\LaravelWebSockets\Statistics\Http\Middleware;

use Gajosu\LaravelWebSockets\Apps\App;

class Authorize
{
    public function handle($request, $next)
    {
        return is_null(App::findBySecret($request->secret)) ? abort(403) : $next($request);
    }
}
