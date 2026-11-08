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

class ArchiveNoticeJob implements ShouldQueue
{
    public function failed(Throwable $exception): void
    {
        $handler = new class {
            public function handle(): void
            {
                app(Notices::class)->revert();
            }
        };

        if (!class_exists(NoticeArchiver::class)) {
            class NoticeArchiver
            {
                public function handle(): void
                {
                    resolve(Notices::class)->archive();
                }
            }
        }
    }
}

trait RevertsNotices
{
    public function failed(Throwable $exception): void
    {
        app(Notices::class)->revert();
    }
}
