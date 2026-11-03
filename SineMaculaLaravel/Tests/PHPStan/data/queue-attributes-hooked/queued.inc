<?php

namespace PreferQueueAttributesHooked;

use Illuminate\Contracts\Queue\ShouldQueue;

class EscalatesUrgentWork implements ShouldQueue
{
    public bool $urgent = false;

    public int $tries = 3 {
        get => $this->urgent ? 1 : $this->tries;
    }

    public int $timeout = 60;
}
