<?php

namespace App\Services;

class LegacyTask
{
    use \Illuminate\Foundation\Bus\Dispatchable;
}

use Illuminate\Foundation\Events\Dispatchable;

class OrderShipped
{
    use Dispatchable;
}
