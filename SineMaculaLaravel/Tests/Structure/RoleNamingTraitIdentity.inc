<?php

namespace App\Domain;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CleanupTask
{
    use Dispatchable;
}

class ReportRunner
{
    use Dispatchable;
    use SerializesModels;
}

class InvoiceRunner
{
    use SerializesModels;
    use Dispatchable;
}

class ArchiveRunner
{
    use SerializesModels, Dispatchable;
}

class BackupRunner
{
    use \Illuminate\Foundation\Bus\Dispatchable;
}

class PruneLogs
{use Dispatchable;}
