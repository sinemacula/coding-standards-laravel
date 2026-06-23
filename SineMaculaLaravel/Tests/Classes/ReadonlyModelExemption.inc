<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Thing extends Model
{
    public $timestamps = false;

    public $incrementing = false;
}

class Account extends Authenticatable
{
    public $timestamps = false;
}

class RoleMember extends Pivot
{
    public $incrementing = false;
}

class PlainObject
{
    public $value;
}
