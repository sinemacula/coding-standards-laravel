<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

class SendReport implements ShouldQueue
{
    public $tries = 3;

    public $delay = 30;
}

class ReportMail extends Mailable
{
    public $timeout = 60;

    public $failOnTimeout = true;
}
