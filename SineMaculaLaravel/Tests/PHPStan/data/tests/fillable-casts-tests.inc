<?php

namespace SineMaculaLaravel\Tests\PHPStan\Data;

use Illuminate\Database\Eloquent\Model;

class UncastTestModel extends Model
{
    protected $fillable = ['name'];
}
