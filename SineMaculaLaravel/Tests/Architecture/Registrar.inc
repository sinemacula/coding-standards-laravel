<?php

namespace App\Container;

use Illuminate\Support\ServiceProvider;

class LifecycleRegistrar
{
    public function boot(): void
    {
        $logger = app(Logger::class);
    }
}

class ContainerKernel extends ServiceProvider
{
    public function boot(): void
    {
        $logger = resolve(Logger::class);
    }
}
