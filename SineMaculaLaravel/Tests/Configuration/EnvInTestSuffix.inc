<?php

namespace App\Tests;

class ConfigServiceTest
{
    public function test_reads_env(): void
    {
        $value = env('CACHE_DRIVER');
    }
}
