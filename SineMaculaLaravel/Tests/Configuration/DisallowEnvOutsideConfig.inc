<?php

namespace App\Services;

use Illuminate\Contracts\Config\Repository;

class EnvReader
{
    public function read(Repository $config): string
    {
        $a = env('APP_NAME');
        $b = config('app.name');
        $c = $config->env('FALLBACK');
        $d = ENV('APP_KEY');

        return $a . $b . $c . $d;
    }
}
