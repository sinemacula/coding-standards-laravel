<?php

namespace App\Services;

use const Illuminate\Foundation\Events\Dispatchable;
use function Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Foundation\Bus;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\Dispatchable as JobTrait;
use Illuminate\Foundation\Bus\{Dispatchable as GroupJob, PendingDispatch};
use Illuminate\Foundation\Events\Dispatchable as EventDispatch;
use Illuminate\Foundation\MiniBus\Dispatchable as MiniBusJob;
use Illuminate\Queue\SerializesModels;

class PlainImport
{
    use Dispatchable;
}

class AliasedImport
{
    use JobTrait;
}

class GroupImport
{
    use GroupJob;
}

class NamespaceHead
{
    use Bus\Dispatchable;
}

class FullyQualified
{
    use \Illuminate\Foundation\Bus\Dispatchable;
}

class AliasedEvent
{
    use EventDispatch, SerializesModels;
}

class RelativeUnimported
{
    use Queue\Dispatchable;
}

class SegmentBoundary
{
    use MiniBusJob;
}
