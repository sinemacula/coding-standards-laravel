<?php

namespace App\Domain;

use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Routing\Controller;

class Foo extends Controller
{
}

class FooController extends Controller
{
}

class User extends Model
{
}

class UserModel extends Model
{
}

class Account extends Authenticatable
{
}

class SendEmails extends Command
{
}

class ProcessUpload implements ShouldQueue
{
}

class Welcome extends Mailable
{
}

class CleanupTask
{
    use Dispatchable;
}

class Helper
{
}

/**
 * Documented but roleless.
 */
class DocumentedHelper
{
}
