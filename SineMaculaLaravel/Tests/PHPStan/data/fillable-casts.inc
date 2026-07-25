<?php

namespace SineMaculaLaravel\Tests\PHPStan\Data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FullyCast extends Model
{
    protected $fillable = ['name', 'email'];

    protected function casts(): array
    {
        return [
            'name'  => 'string',
            'email' => 'string',
        ];
    }
}

class PartiallyCast extends Model
{
    protected $fillable = ['name', 'email', 'active'];

    protected function casts(): array
    {
        return [
            'name' => 'string',
        ];
    }
}

class NoCasts extends Model
{
    protected $fillable = ['title'];
}

#[Fillable(['reference', 'quantity'])]
class AttributeFillable extends Model
{
    protected function casts(): array
    {
        return [
            'reference' => 'string',
        ];
    }
}

class CastViaProperty extends Model
{
    protected $fillable = ['status'];

    protected $casts = [
        'status' => 'string',
    ];
}

class MergesParentCasts extends Model
{
    protected $fillable = ['inherited'];

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'local' => 'string',
        ]);
    }
}

class SpreadsParentCasts extends Model
{
    protected $fillable = ['inherited'];

    protected function casts(): array
    {
        return [
            'local' => 'string',
            ...parent::casts(),
        ];
    }
}

class DynamicFillable extends Model
{
    protected $fillable = self::FIELDS;

    protected function casts(): array
    {
        return [];
    }
}

class NoFillable extends Model
{
    protected function casts(): array
    {
        return [];
    }
}

class Membership extends Pivot
{
    protected $fillable = ['role'];
}

abstract class BaseModel extends Model
{
    protected $fillable = ['ignored'];
}

class DomainObject
{
    protected $fillable = ['ignored'];
}

class Subclass extends BaseModel
{
    protected $fillable = ['ignored'];
}

class CapitalCastsMethod extends Model
{
    protected $fillable = ['code'];

    protected function Casts(): array
    {
        return ['code' => 'string'];
    }
}

#[Fillable(['reference'])]
class DynamicPropertyWithAttribute extends Model
{
    protected $fillable = self::FIELDS;
}

class NonLiteralCastsProperty extends Model
{
    protected $fillable = ['flag'];

    protected $casts = self::CAST_MAP;
}

class CastsAfterOtherProperty extends Model
{
    protected $fillable = ['status'];

    protected $connection = 'main';

    protected $casts = ['status' => 'string'];
}

class NonReturnCastsBody extends Model
{
    protected $fillable = ['first', 'second'];

    protected function casts(): array
    {
        return ['first' => 'string'];

        return ['second' => 'string'];
    }
}
