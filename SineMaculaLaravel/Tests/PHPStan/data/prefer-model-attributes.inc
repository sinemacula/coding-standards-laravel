<?php

namespace SineMaculaLaravel\Tests\PHPStan\Data;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $hidden = ['secret', 'token'];

    protected $touches = ['author'];

    protected $fillable = ['title'];

    public function newFactory()
    {
    }

    public function publish(): void
    {
    }
}

class Comment extends Model
{
    public function newCollection()
    {
    }

    public function newEloquentBuilder()
    {
    }
}

class Account extends Model
{
    protected $hidden = ['a', 'b', 'c', 'd', 'e', 'f'];
}

class PlainObject
{
    protected $table = 'plain';

    public function newFactory()
    {
    }
}

class Draft extends Model
{
    protected $touches = ['author'], $fillable = ['draft_title'];

    public function refresh(): void
    {
    }

    public function newCollection()
    {
    }
}

class Wide extends Model
{
    protected $fillable = ['a', 'b', 'c', 'd', 'e', 'f'];
}

class Five extends Model
{
    protected $hidden = ['a', 'b', 'c', 'd', 'e'];
}
