<?php

namespace App\Domain;

use Illuminate\Routing\Controller;

/**
 * @role-exempt
 */
class Foo extends Controller
{
}

#[NotARole]
class Bar extends Controller
{
}

#[SomeMeta(1)]
class BazController extends Controller
{
}
