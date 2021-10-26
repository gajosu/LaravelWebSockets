<?php

namespace Gajosu\LaravelWebSockets\Apps;

interface AppProvider
{
    /**  @return array[Gajosu\LaravelWebSockets\AppProviders\App] */
    public function all(): array;

    public function findById($appId): ?App;

    public function findByKey(string $appKey): ?App;

    public function findBySecret(string $appSecret): ?App;
}
