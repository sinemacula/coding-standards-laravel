<?php

namespace PreferQueueAttributes;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

enum Lane: string
{
    case Mail = 'mail';
}

class SendPodcastAlerts implements ShouldQueue
{
    private const int LIMIT = 2;

    public $episode = 'pilot', $tries = 3;

    public $timeout = 120;

    public $backoff = [1, 5, 10];

    public $maxExceptions = self::LIMIT;

    public $failOnTimeout = true;

    public $deleteWhenMissingModels = TRUE;

    public $uniqueFor = 3600;

    public $queue = Lane::Mail;

    public $connection = 'redis';

    public $delay = 30;

    public $debounceFor = 5;
}

class LeavesItAlone implements ShouldQueue
{
    public $tries;

    public $timeout = null;

    public $failOnTimeout = false;

    public $deleteWhenMissingModels = false;

    public $backoff = 'linear';

    public $connection = 30;

    public $uniqueFor = 1.5;

    private $maxExceptions = 10;

    protected $queue = 'reporting';

    public static $delay = 30;
}

class TriesFromAMethod implements ShouldQueue
{
    public $tries = 3;

    public $backoff = 5;

    public function tries(): int
    {
        return $this->tries;
    }

    public function backoff(): int
    {
        return $this->backoff;
    }
}

abstract class ChainableJob implements ShouldQueue
{
    use Queueable;
}

class ImportFeed extends ChainableJob
{
    public $queue = 'imports';

    public $connection = 'redis';

    public $delay = 30;

    public $tries = 4;
}

class WelcomeMail extends Mailable implements ShouldQueue
{
    public $tries = 2;

    public $failOnTimeout = true;

    public $uniqueFor = 60;
}

class InvoicePaid extends Notification
{
    public $timeout = 30;

    public $failOnTimeout = true;

    public $delay = ['mail' => 60, 'sms' => 0];
}

class OrderShipped implements ShouldBroadcast
{
    public $tries = 1;

    public $queue = 'broadcasts';

    public $uniqueFor = 10;

    public $failOnTimeout = true;

    public function broadcastOn(): array
    {
        return [];
    }
}

class NotQueued
{
    public $tries = 3;
}

class RoutesItself implements ShouldQueue
{
    public $queue = 'reports';

    public $connection = 'redis';

    public $delay = 10;

    public function viaQueue(): string
    {
        return $this->queue;
    }

    public function viaConnection(): string
    {
        return $this->connection;
    }

    public function withDelay(): int
    {
        return $this->delay;
    }
}

class RoutesPerChannel extends Notification
{
    public $queue = 'notices';

    public $connection = 'redis';

    public function viaQueues(): array
    {
        return ['mail' => $this->queue];
    }

    public function viaConnections(): array
    {
        return ['mail' => $this->connection];
    }
}
