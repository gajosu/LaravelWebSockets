<?php

namespace Gajosu\LaravelWebSockets;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Gajosu\LaravelWebSockets\Server\Router;
use Gajosu\LaravelWebSockets\Apps\AppProvider;
use Gajosu\LaravelWebSockets\Queue\AsyncRedisConnector;
use Gajosu\LaravelWebSockets\WebSockets\Channels\ChannelManager;
use Gajosu\LaravelWebSockets\Dashboard\Http\Controllers\SendMessage;
use Gajosu\LaravelWebSockets\Dashboard\Http\Controllers\ShowDashboard;
use Gajosu\LaravelWebSockets\Dashboard\Http\Controllers\AuthenticateDashboard;
use Gajosu\LaravelWebSockets\Dashboard\Http\Controllers\DashboardApiController;
use Gajosu\LaravelWebSockets\WebSockets\Channels\ChannelManagers\ArrayChannelManager;
use Gajosu\LaravelWebSockets\Dashboard\Http\Middleware\Authorize as AuthorizeDashboard;
use Gajosu\LaravelWebSockets\Statistics\Http\Middleware\Authorize as AuthorizeStatistics;
use Gajosu\LaravelWebSockets\Statistics\Http\Controllers\WebSocketStatisticsEntriesController;

class WebSocketsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/websockets.php' => base_path('config/websockets.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/0000_00_00_000000_create_websockets_statistics_entries_table.php' => database_path('migrations/0000_00_00_000000_create_websockets_statistics_entries_table.php'),
        ], 'migrations');

        $this
            ->registerRoutes()
            ->registerDashboardGate()
            ->registerAsyncRedisQueueDriver();

        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'websockets');

        $this->commands([
            Console\StartWebSocketServer::class,
            Console\CleanStatistics::class,
            Console\RestartWebSocketServer::class,
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/websockets.php', 'websockets');

        $this->app->singleton('websockets.router', function () {
            return new Router();
        });

        $this->app->singleton(ChannelManager::class, function ($app) {
            $config = $app['config']['websockets'];

            return ($config['channel_manager'] ?? null) !== null && class_exists($config['channel_manager'])
                ? app($config['channel_manager']) : new ArrayChannelManager();
        });

        $this->app->singleton(AppProvider::class, function ($app) {
            $config = $app['config']['websockets'];

            return app($config['app_provider']);
        });
    }

    protected function registerRoutes()
    {
        Route::prefix(config('websockets.path'))->group(function () {
            Route::middleware(config('websockets.middleware', [AuthorizeDashboard::class]))->group(function () {
                Route::get('/', ShowDashboard::class);
                Route::get('/api/{appId}/statistics', [DashboardApiController::class,  'getStatistics']);
                Route::post('auth', AuthenticateDashboard::class);
                Route::post('event', SendMessage::class);
            });

            Route::middleware(AuthorizeStatistics::class)->group(function () {
                Route::post('statistics', [WebSocketStatisticsEntriesController::class, 'store']);
            });
        });

        return $this;
    }

    protected function registerDashboardGate()
    {
        Gate::define('viewWebSocketsDashboard', function ($user = null) {
            return app()->environment('local');
        });

        return $this;
    }

    /**
     * Register the async, non-blocking Redis queue driver.
     *
     * @return void
     */
    protected function registerAsyncRedisQueueDriver()
    {
        Queue::extend('async-redis', function () {
            return new AsyncRedisConnector($this->app['redis']);
        });

        return $this;
    }



}
