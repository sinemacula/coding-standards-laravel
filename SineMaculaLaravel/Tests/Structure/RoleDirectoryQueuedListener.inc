<?php

namespace App\Listeners\Billing;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInvoiceListener implements ShouldQueue
{
    use InteractsWithQueue;
}
