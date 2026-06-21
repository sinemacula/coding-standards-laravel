<?php

namespace SineMaculaLaravel\Tests\PHPStan\Data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Post extends Model
{
}

class User extends Model
{
    protected $fillable = ['name'];
}

class Tag extends Model
{
    protected $guarded = [];
}

abstract class BaseModel extends Model
{
}

class PlainObject
{
}

class RoleUser extends Pivot
{
}
