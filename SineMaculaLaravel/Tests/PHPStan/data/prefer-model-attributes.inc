<?php

namespace SineMaculaLaravel\Tests\PHPStan\Data;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $hidden = ['secret', 'token'];

    protected $touches = ['author'];

    protected $fillable = ['title'];
}

class Account extends Model
{
    protected $hidden = ['a', 'b', 'c', 'd', 'e', 'f'];
}

class PlainObject
{
    protected $table = 'plain';
}
