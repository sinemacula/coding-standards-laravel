<?php

namespace SineMaculaLaravel\Tests\PHPStan\Data;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
}

function makeAnonymousModel(): Model
{
    return new class extends Model {
    };
}
