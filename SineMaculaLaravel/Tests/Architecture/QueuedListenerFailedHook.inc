<?php

namespace App\Listeners;

use App\Services\Notices;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

class RevertNoticeListener implements ShouldQueue
{
    public function failed(Throwable $exception): void
    {
        app(Notices::class)->revert();
    }
}
