<?php

namespace App\Domain;

use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessUpload implements ShouldQueue
{
}

class CleanupJob implements ShouldQueue
{
}
