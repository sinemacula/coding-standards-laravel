<?php

namespace App\Domain;

use App\Services\Notices;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Throwable;

class SendNoticeJob implements ShouldQueue
{
    public function handle(): void
    {
        app(Notices::class)->send();
    }

    public function failed(Throwable $exception): void
    {
        app(Notices::class)->revert();

        collect([1])->each(fn () => resolve(Notices::class)->log());
    }
}

class RetryNoticeJob implements ShouldQueue
{
    public function Failed(Throwable $exception): void
    {
        app(Notices::class)->revert();
    }
}

class NoticeMail extends Mailable implements ShouldQueue
{
    public function failed(Throwable $exception): void
    {
        app(Notices::class)->revert();
    }
}

class NoticeSent extends Notification implements ShouldQueue
{
    public function failed(Throwable $exception): void
    {
        app(Notices::class)->revert();
    }
}

class NoticeSender
{
    public function failed(Throwable $exception): void
    {
        app(Notices::class)->revert();
    }
}
