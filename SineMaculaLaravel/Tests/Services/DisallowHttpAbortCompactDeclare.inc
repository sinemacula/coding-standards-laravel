<?php declare(strict_types = 1);namespace App\Services;

class TerseService
{
    public function fail(): void
    {
        abort(503);
    }
}
