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

#[NotARole]class Snug extends Controller
{
}

/**
 * @role-exempt
 */class Tight extends Controller
{
}

/**
 * @ROLE-EXEMPT
 */
class Grault extends Controller
{
}

#[SomeMeta(3)]#[NotARole]
class Adjacent extends Controller
{
}

#[SomeMeta(1), NotARole]
class Corge extends Controller
{
}

/**
 * @role-exempt
 */
#[SomeMeta(4)]
final class Attributed extends Controller
{
}

/**
 * @role-exempt
 */
#[SomeMeta(5)]
#[OtherMeta]
abstract class Stacked extends Controller
{
}

#[\SineMacula\CodingStandardsLaravel\Attributes\NotARole]
class Qualified extends Controller
{
}
