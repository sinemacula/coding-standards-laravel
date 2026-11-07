<?php

namespace App\Domain;

use Illuminate\Contracts\Queue\ShouldQueue;

#[AsCommand(name: 'app:sync')]
class SyncFeed implements ShouldQueue
{
}
