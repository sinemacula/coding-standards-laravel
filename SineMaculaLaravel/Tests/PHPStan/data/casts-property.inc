<?php

namespace SineMaculaLaravel\Tests\PHPStan\Data;

use Illuminate\Database\Eloquent\Model;

class CastsModel extends Model
{
    protected $casts = ['active' => 'bool'];
}

class PlainModel extends Model
{
    protected $table = 'plain';
}

final class AttributeSetter
{
    private $casts = [];
}

class AddressLink extends \Illuminate\Database\Eloquent\Relations\MorphPivot
{
    protected $casts = ['primary' => 'bool'];
}
