<?php

namespace App;

class TestHelpers
{
    public function bootstrap(): void
    {
        $value = env('DB_CONNECTION');
    }
}
