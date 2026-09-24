<?php

namespace App\Domain;

use Illuminate\Routing\Controller;

#[AsCommand(name: 'app:send')]
class SendEmails extends Controller
{
}
