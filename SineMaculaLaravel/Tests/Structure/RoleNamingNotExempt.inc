<?php

namespace App\Domain;

use Illuminate\Routing\Controller;

/**
 * @role-exempt
 */
class Skipped extends Controller
{
}

/**
 * @internal
 */
class Waldo extends Controller
{
}

#[SomeMeta(NotARole)]
class Quux extends Controller
{
}

/**
 * @internal
 */
#[SomeMeta(2)]
class Fred extends Controller
{
}
