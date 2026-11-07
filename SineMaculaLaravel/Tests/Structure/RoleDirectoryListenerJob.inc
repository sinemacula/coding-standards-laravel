<?php

namespace App\Listeners\Billing;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ChargeCustomer implements ShouldQueue
{
    use Dispatchable;
}

class ReconcileLedger
{
    use Dispatchable;
}
