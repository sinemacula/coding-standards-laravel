<?php

namespace App\Tests;

use Orchestra\Testbench\TestCase;

class BootstrapsTheApplication extends TestCase
{
    public function setUp(): void
    {
        $value = env('APP_ENV');
    }
}
