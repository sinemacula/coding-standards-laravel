<?php

namespace App\Services;

use Illuminate\Support\Facades\App;

class UserService
{
    public function viaHelper(): void
    {
        $logger = app(Logger::class);
    }

    public function viaResolve(): void
    {
        $logger = resolve(Logger::class);
    }

    public function viaFacade(): void
    {
        $logger = App::make(Logger::class);
    }

    public function injected(Logger $logger): void
    {
        $logger->info('ok');
    }

    public function notServiceLocation(Factory $factory): void
    {
        $value = $this->app;
        $thing = $factory->make('x');
    }

    public function dynamicHelper(string $class): void
    {
        $logger = app($class);
    }

    public function dynamicChain(string $class): void
    {
        $logger = app()->make($class);
    }

    public function dynamicFacade(string $class): void
    {
        $logger = App::make($class);
    }
}

trait LocatesServices
{
    public function locate(): void
    {
        $logger = app(Logger::class);
    }
}

function appHelperOutsideClass(): void
{
    $x = app(Foo::class);
}

class MiscService
{
    public function methodNamedResolve(): void
    {
        $this->resolve('thing');
    }

    public function facadeEnvironment(): void
    {
        App::environment('production');
    }

    public function facadeMakeWith(): void
    {
        $logger = App::makeWith(Logger::class, ['level' => 'debug']);
    }

    public function propertyNamedApp(Container $container): void
    {
        $container->App->make(Logger::class);
    }
}
