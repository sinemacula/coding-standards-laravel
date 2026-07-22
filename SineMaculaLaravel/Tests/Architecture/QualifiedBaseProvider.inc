<?php

namespace App\Wiring;

class ContainerBindings extends \Illuminate\Support\ServiceProvider
{
    public function boot(): void
    {
        $logger = app(Logger::class);
    }
}
