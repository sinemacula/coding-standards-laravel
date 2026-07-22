<?php

namespace SineMaculaLaravel\Tests\PHPStan\Data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function recent()
    {
        return $this->hasMany(Post::class)->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function counter()
    {
        return $this->newQuery()->count();
    }

    public function label()
    {
        return $this->title;
    }

    public function viaBuilder()
    {
        $builder = new PostBuilder();

        return $builder->hasMany(Post::class);
    }
}
