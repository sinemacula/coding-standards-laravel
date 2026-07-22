<?php

namespace App\Domain;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Routing\Controller;

/**
 * @role-exempt
 */
class Dashboard extends Controller
{
}

class Billing extends Controller
{
}

class AccountModel implements Authenticatable
{
}
