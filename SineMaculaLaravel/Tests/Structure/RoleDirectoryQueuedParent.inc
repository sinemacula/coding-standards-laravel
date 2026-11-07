<?php

namespace App\Domain;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

abstract class QueuedNotification extends Notification implements ShouldQueue
{
}

class InvoiceMail extends Mailable implements ShouldQueue
{
}

class RebuildIndex implements ShouldQueue
{
}
